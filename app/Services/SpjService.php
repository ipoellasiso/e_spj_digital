<?php

namespace App\Services;

use App\Enums\SpjStatus;
use App\Exceptions\SpjException;
use App\Models\AnggaranRekening;
use App\Models\DpaVersi;
use App\Models\Spj;
use App\Models\SpjApproval;
use App\Models\User;
use App\Support\Uang;
use Illuminate\Support\Facades\DB;

/** Aturan bisnis SPJ tahap 3: simpan draft, ajukan (menahan pagu dan memberi nomor), hapus draft. */
class SpjService
{
    public function __construct(private PaguService $pagu, private NomorService $nomor, private DokumenSpjService $dokumen)
    {
    }

    /**
     * Membuat atau memperbarui SPJ yang masih bisa diubah (draft atau dikembalikan).
     *
     * @param  array  $data   jenis, tanggal, anggaran_rekening_id, sumber_dana_id, uraian, penyedia_id, kolom pejabat
     * @param  array<int, array{uraian:string, volume:mixed, satuan:string, harga_satuan:mixed}>  $items
     */
    public function simpan(array $data, array $items, User $user, ?Spj $spj = null): Spj
    {
        if ($spj && ! $spj->status->bisaDiubah()) {
            throw new SpjException('SPJ yang sudah diajukan tidak bisa diubah.');
        }

        // Honor/insentif: satu item = satu penerima (volume 1 orang), sehingga formulir cukup mengirim honor sebagai harga.
        if (($data['kategori'] ?? $spj?->kategori) === 'honor_lembur') {
            $items = array_map(fn ($i) => $i + ['volume' => '1', 'satuan' => 'Orang'], $items);
        }
        $baris = $this->hitungItem($items);
        $ar = AnggaranRekening::withoutGlobalScopes()->findOrFail($data['anggaran_rekening_id']);
        $versi = DpaVersi::withoutGlobalScopes()->findOrFail($ar->dpa_versi_id);
        if (! $versi->aktif) {
            throw new SpjException('Rekening ini bukan bagian dari DPA aktif.');
        }

        return DB::transaction(function () use ($data, $baris, $user, $spj, $ar, $versi) {
            $total = array_sum(array_column($baris, 'jumlah_sen'));

            $isi = array_intersect_key($data, array_flip([
                'jenis', 'kategori', 'tanggal', 'anggaran_rekening_id', 'sumber_dana_id', 'uraian', 'penyedia_id', ...array_keys(Spj::PEJABAT),
            ])) + ['total' => Uang::format($total), 'tahun' => $versi->tahun];

            if ($spj) {
                $spj->update($isi);
                $spj->items()->delete();
                $this->dokumen->hapus($spj); // isi berubah: dokumen lama tidak berlaku lagi
            } else {
                $spj = Spj::create($isi + ['opd_id' => $ar->opd_id, 'dibuat_oleh' => $user->id, 'status' => SpjStatus::Draft]);
            }

            $spj->items()->createMany(array_map(fn (array $b) => [
                'anggaran_rekening_id' => $b['anggaran_rekening_id'],
                'uraian' => $b['uraian'], 'volume' => Uang::format($b['volume_seratus']), 'satuan' => $b['satuan'],
                'harga_satuan' => Uang::format($b['harga_sen']), 'jumlah' => Uang::format($b['jumlah_sen']),
                'jabatan' => $b['jabatan'], 'nama_bank' => $b['nama_bank'], 'no_rekening' => $b['no_rekening'],
                'pajak' => Uang::format($b['pajak_sen']),
            ], $baris));

            return $spj->refresh();
        });
    }

    /**
     * Mengajukan SPJ: semua isian wajib harus lengkap, pagu rekening ditahan secara atomik,
     * nomor urut diberikan (hanya sekali), dan status menjadi diajukan.
     */
    public function ajukan(Spj $spj, User $user): Spj
    {
        $this->periksaKelengkapan($spj);

        return DB::transaction(function () use ($spj, $user) {
            $s = Spj::withoutGlobalScopes()->lockForUpdate()->findOrFail($spj->id);
            if (! $s->status->bisaDiubah()) {
                throw new SpjException('SPJ ini sudah diajukan.');
            }

            $this->pagu->reserve($s); // melempar PaguTidakCukupException; transaksi batal, nomor tidak terpakai

            if (! $s->nomor_urut) {
                $s->nomor_urut = $this->nomor->berikutnya($s->opd_id, $s->tahun);
            }
            $s->status = SpjStatus::Diajukan;
            $s->save();

            SpjApproval::create(['spj_id' => $s->id, 'tahap' => 'pengajuan', 'aksi' => 'diajukan', 'user_id' => $user->id]);

            // Nomor dokumen dan penandatangan dikunci (snapshot) saat diajukan. Kegagalan membuat dokumen tidak
            // membatalkan pengajuan; dokumen akan dibuat otomatis saat pertama kali dicetak.
            try {
                $this->dokumen->buat($s);
            } catch (\Throwable $e) {
                report($e);
            }

            return $s->refresh();
        });
    }

    /** Hanya draft yang belum pernah diberi nomor yang boleh dihapus. */
    public function hapus(Spj $spj): void
    {
        if ($spj->status !== SpjStatus::Draft || $spj->nomor_urut !== null) {
            throw new SpjException('Hanya draft yang belum pernah diajukan yang bisa dihapus.');
        }
        $spj->delete();
    }

    /** @return array<int, array{uraian:string, satuan:string, volume_seratus:int, harga_sen:int, jumlah_sen:int}> */
    public function hitungItem(array $items): array
    {
        $hasil = [];
        foreach (array_values($items) as $i => $item) {
            $volume = Uang::volumeSeratus($item['volume'] ?? null);
            $harga = Uang::dariInput($item['harga_satuan'] ?? null);
            $uraian = trim((string) ($item['uraian'] ?? ''));
            $satuan = trim((string) ($item['satuan'] ?? ''));
            $no = $i + 1;

            if ($uraian === '' || $satuan === '') {
                throw new SpjException("Item {$no}: uraian dan satuan wajib diisi.");
            }
            if ($volume === null || $volume <= 0) {
                throw new SpjException("Item {$no}: volume harus angka lebih dari 0 (maksimal 2 desimal).");
            }
            if ($harga === null || $harga <= 0) {
                throw new SpjException("Item {$no}: harga satuan harus angka lebih dari 0.");
            }
            $jumlah = Uang::jumlahSen($harga, $volume);
            $pajakInput = trim((string) ($item['pajak'] ?? ''));
            $pajak = $pajakInput === '' ? 0 : Uang::dariInput($pajakInput);
            if ($pajak === null || $pajak < 0) {
                throw new SpjException("Item {$no}: pajak harus angka (boleh 0).");
            }
            if ($pajak > $jumlah) {
                throw new SpjException("Item {$no}: pajak tidak boleh lebih besar dari honor.");
            }
            $hasil[] = [
                'anggaran_rekening_id' => $item['anggaran_rekening_id'] ?? null,
                'uraian' => $uraian, 'satuan' => $satuan, 'volume_seratus' => $volume,
                'harga_sen' => $harga, 'jumlah_sen' => $jumlah, 'pajak_sen' => $pajak,
                'jabatan' => ($j = trim((string) ($item['jabatan'] ?? ''))) === '' ? null : $j,
                'nama_bank' => ($j = trim((string) ($item['nama_bank'] ?? ''))) === '' ? null : $j,
                'no_rekening' => ($j = trim((string) ($item['no_rekening'] ?? ''))) === '' ? null : $j,
            ];
        }
        if (! $hasil) {
            throw new SpjException('Isi minimal satu item belanja.');
        }

        return $hasil;
    }

    private function periksaKelengkapan(Spj $spj): void
    {
        if (! $spj->status->bisaDiubah()) {
            throw new SpjException('SPJ ini sudah diajukan.');
        }

        $masalah = [];
        if ($spj->items()->count() === 0 || Uang::sen($spj->total) <= 0) {
            $masalah[] = 'Item belanja belum diisi.';
        }
        $kategori = $spj->kategoriInfo();
        if ($kategori['penyedia'] && ! $spj->penyedia_id) {
            $masalah[] = 'Penyedia belum dipilih.';
        }
        foreach ($kategori['pejabat'] as $kolom) {
            if (! $spj->{$kolom}) {
                $masalah[] = Spj::PEJABAT[$kolom][0] . ' belum dipilih.';
            }
        }
        $ar = AnggaranRekening::withoutGlobalScopes()->find($spj->anggaran_rekening_id);
        $versi = $ar ? DpaVersi::withoutGlobalScopes()->find($ar->dpa_versi_id) : null;
        if (! $versi || ! $versi->aktif) {
            $masalah[] = 'Rekening yang dipilih bukan bagian dari DPA aktif.';
        }

        if ($masalah) {
            throw new SpjException($masalah);
        }
    }
}