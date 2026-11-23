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
    public function __construct(private PaguService $pagu, private NomorService $nomor)
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

        $baris = $this->hitungItem($items);
        $ar = AnggaranRekening::withoutGlobalScopes()->findOrFail($data['anggaran_rekening_id']);
        $versi = DpaVersi::withoutGlobalScopes()->findOrFail($ar->dpa_versi_id);
        if (! $versi->aktif) {
            throw new SpjException('Rekening ini bukan bagian dari DPA aktif.');
        }

        return DB::transaction(function () use ($data, $baris, $user, $spj, $ar, $versi) {
            $total = array_sum(array_column($baris, 'jumlah_sen'));

            $isi = array_intersect_key($data, array_flip([
                'jenis', 'tanggal', 'anggaran_rekening_id', 'sumber_dana_id', 'uraian', 'penyedia_id', ...array_keys(Spj::PEJABAT),
            ])) + ['total' => Uang::format($total), 'tahun' => $versi->tahun];

            if ($spj) {
                $spj->update($isi);
                $spj->items()->delete();
            } else {
                $spj = Spj::create($isi + ['opd_id' => $ar->opd_id, 'dibuat_oleh' => $user->id, 'status' => SpjStatus::Draft]);
            }

            $spj->items()->createMany(array_map(fn (array $b) => [
                'uraian' => $b['uraian'], 'volume' => Uang::format($b['volume_seratus']), 'satuan' => $b['satuan'],
                'harga_satuan' => Uang::format($b['harga_sen']), 'jumlah' => Uang::format($b['jumlah_sen']),
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
            $hasil[] = [
                'uraian' => $uraian, 'satuan' => $satuan, 'volume_seratus' => $volume,
                'harga_sen' => $harga, 'jumlah_sen' => Uang::jumlahSen($harga, $volume),
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
        if (! $spj->penyedia_id) {
            $masalah[] = 'Penyedia belum dipilih.';
        }
        foreach (Spj::PEJABAT as $kolom => [$label]) {
            if (! $spj->{$kolom}) {
                $masalah[] = "{$label} belum dipilih.";
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