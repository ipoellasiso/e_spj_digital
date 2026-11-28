<?php

namespace App\Services;

use App\Enums\SpjStatus;
use App\Models\PejabatOpd;
use App\Models\SkPenunjukan;
use App\Models\Spj;
use App\Models\SpjDokumen;
use App\Support\Nip;
use App\Support\TanggalIndo;
use App\Support\Terbilang;
use App\Support\Uang;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Dokumen cetak SPJ. Keenam dokumen dibuat sekali saat SPJ diajukan (nomor, tanggal, penandatangan, SK, dan
 * data anggaran disalin ke snapshot), sehingga pergantian pejabat atau perubahan master sesudahnya tidak
 * mengubah dokumen yang sudah beredar. Bila SPJ dikembalikan lalu diubah dan diajukan lagi, dokumen dibuat ulang
 * dengan nomor urut yang sama.
 */
class DokumenSpjService
{
    /** slug => kode di nomor dokumen, judul, dan view */
    public const JENIS = [
        'nota-pesanan' => ['kode' => 'NP', 'judul' => 'Nota Pesanan', 'view' => 'dokumen.halaman.nota-pesanan'],
        'ba-pengadaan' => ['kode' => 'BAPP', 'judul' => 'Berita Acara Pengadaan Barang', 'view' => 'dokumen.halaman.ba-pengadaan'],
        'ba-pemeriksaan' => ['kode' => 'BAPB', 'judul' => 'Berita Acara Pemeriksaan Barang', 'view' => 'dokumen.halaman.ba-pemeriksaan'],
        'ba-penerimaan' => ['kode' => 'BA-TRM', 'judul' => 'Berita Acara Penerimaan Barang', 'view' => 'dokumen.halaman.ba-penerimaan'],
        'bast' => ['kode' => 'BAST', 'judul' => 'Berita Acara Penyerahan Barang', 'view' => 'dokumen.halaman.bast'],
        'kwitansi' => ['kode' => 'KW', 'judul' => 'Tanda Penerimaan (Kwitansi)', 'view' => 'dokumen.halaman.kwitansi'],
        'daftar-penerima' => ['kode' => 'DP', 'judul' => 'Daftar Penerima', 'view' => 'dokumen.halaman.daftar-penerima'],
    ];

    /** Dokumen yang dicetak lanskap (selain itu potret). Dompdf tidak bisa mencampur orientasi dalam satu PDF. */
    public const ORIENTASI = ['daftar-penerima' => 'landscape'];

    public static function orientasi(string $slug): string
    {
        return self::ORIENTASI[$slug] ?? 'portrait';
    }

    /** dokumen => dokumen yang dirujuknya */
    private const INDUK = [
        'ba-pengadaan' => 'nota-pesanan', 'ba-pemeriksaan' => 'nota-pesanan', 'ba-penerimaan' => 'nota-pesanan',
        'bast' => 'ba-pemeriksaan', 'kwitansi' => 'nota-pesanan',
    ];

    private const SK = [
        'pengadaan' => 'Penunjukan Pejabat Pengadaan',
        'ppk' => 'Penunjukan Pejabat Pembuat Komitmen',
        'pengurus_barang' => 'Penunjukan Pengurus Barang',
    ];

    public function __construct(private NomorService $nomor)
    {
    }

    /** Dokumen yang berlaku untuk kategori belanja SPJ ini, dalam urutan cetak. */
    public function slugUntuk(Spj $spj): array
    {
        return $spj->kategoriInfo()['dokumen'];
    }

    /** Dokumen baru dicetak setelah SPJ diajukan; draft, dikembalikan, dan dibatalkan tidak punya dokumen. */
    public function bisaDicetak(Spj $spj): bool
    {
        return $spj->nomor_urut !== null && in_array($spj->status, [
            SpjStatus::Diajukan, SpjStatus::DiverifikasiBendahara, SpjStatus::DiverifikasiPpk, SpjStatus::Final,
        ], true);
    }

    /** Membuat ulang keenam dokumen dari kondisi SPJ saat ini. Dipanggil saat SPJ diajukan. */
    public function buat(Spj $spj): Collection
    {
        return DB::transaction(function () use ($spj) {
            SpjDokumen::where('spj_id', $spj->id)->delete();
            $snapshot = $this->snapshot($spj);

            $dibuat = [];
            foreach ($this->slugUntuk($spj) as $slug) {
                $info = self::JENIS[$slug];
                $target = self::INDUK[$slug] ?? null;
                $dibuat[$slug] = SpjDokumen::create([
                    'spj_id' => $spj->id, 'jenis' => $slug, 'tanggal' => $spj->tanggal->toDateString(),
                    'nomor' => $this->nomor->format((int) $spj->nomor_urut, $info['kode'], $snapshot['opd']['singkatan'], $spj->tanggal),
                    'induk_dokumen_id' => ($target !== null && isset($dibuat[$target])) ? $dibuat[$target]->id : null, // hanya bila dokumen yang dirujuk ikut dicetak
                    'snapshot' => $snapshot,
                    'hasil_pemeriksaan' => $slug === 'ba-pemeriksaan' ? 'baik' : null,
                ]);
            }

            return collect($dibuat);
        });
    }

    public function hapus(Spj $spj): void
    {
        SpjDokumen::where('spj_id', $spj->id)->delete();
    }

    /** Dokumen SPJ ini (dibuat bila belum ada, mis. SPJ yang diajukan sebelum fitur cetak ada). */
    public function pastikan(Spj $spj): Collection
    {
        $ada = SpjDokumen::where('spj_id', $spj->id)->get()->keyBy('jenis');
        $harus = collect($this->slugUntuk($spj))->sort()->values()->all();

        return $ada->keys()->sort()->values()->all() === $harus ? $ada : $this->buat($spj);
    }

    /** Ringkasan untuk panel di halaman detail SPJ. */
    public function ringkasan(Spj $spj): array
    {
        if (! $this->bisaDicetak($spj)) {
            return ['tersedia' => false];
        }
        $dokumen = $this->pastikan($spj);
        $urut = $this->slugUntuk($spj);
        $s = $dokumen[$urut[0]]->snapshot;

        // Peringatan hanya untuk data yang dipakai oleh dokumen kategori ini.
        $peringatan = [];
        $perluSk = ['ba-pengadaan' => ['pengadaan', 'Penunjukan Pejabat Pengadaan'], 'ba-pemeriksaan' => ['ppk', 'Penunjukan Pejabat Pembuat Komitmen'], 'ba-penerimaan' => ['pengurus_barang', 'Penunjukan Pengurus Barang']];
        foreach ($perluSk as $slug => [$kunci, $label]) {
            if (isset($dokumen[$slug]) && empty($s['sk'][$kunci])) {
                $peringatan[] = "SK {$label} tahun {$spj->tahun} belum diisi; di dokumen akan tercetak titik-titik.";
            }
        }
        if (isset($dokumen['nota-pesanan']) && empty($s['anggaran']['nomor_dpa'])) {
            $peringatan[] = 'Nomor DPA belum diisi saat impor; di Nota Pesanan akan tercetak titik-titik.';
        }

        return [
            'tersedia' => true,
            'kategori' => $spj->kategoriInfo()['label'],
            'dokumen' => collect($urut)->map(fn (string $slug) => ['slug' => $slug, 'judul' => self::JENIS[$slug]['judul'], 'nomor' => $dokumen[$slug]->nomor])->all(),
            'peringatan' => $peringatan,
            'bisa_paket' => collect($urut)->map(fn ($x) => self::orientasi($x))->unique()->count() === 1 && count($urut) > 1,
            'hasil_pemeriksaan' => isset($dokumen['ba-pemeriksaan']) ? ($dokumen['ba-pemeriksaan']->hasil_pemeriksaan ?? 'baik') : null,
        ];
    }

    public function ubahHasilPemeriksaan(Spj $spj, string $hasil): void
    {
        $this->pastikan($spj);
        SpjDokumen::where('spj_id', $spj->id)->where('jenis', 'ba-pemeriksaan')->update(['hasil_pemeriksaan' => $hasil]);
    }

    /** Nama berkas unduhan, mis. kwitansi-0263-SPJ-TAWAELI-Apr-2026.pdf */
    public function namaBerkas(Spj $spj, string $slug, SpjDokumen $d): string
    {
        $bagian = explode('/', $d->nomor); // 0263/NP/TAWAELI/Apr/2026

        return sprintf('%s-%s-SPJ-%s-%s-%s.pdf', $slug, $bagian[0], $bagian[2], $bagian[3], $bagian[4]);
    }

    /**
     * Daftar halaman untuk dirender: satu atau semua dokumen.
     *
     * @param  string[]  $slug
     * @return array<int, array{view: string, data: array, pisah: bool}>
     */
    public function halaman(Spj $spj, array $slug): array
    {
        $dokumen = $this->pastikan($spj);
        $spj->loadMissing('items');

        $hasil = [];
        foreach ($slug as $i => $nama) {
            $d = $dokumen[$nama];
            $hasil[] = ['view' => self::JENIS[$nama]['view'], 'data' => $this->data($spj, $d), 'pisah' => $i > 0];
        }

        return $hasil;
    }

    // ---------------------------------------------------------------- internal

    private function data(Spj $spj, SpjDokumen $d): array
    {
        $s = $d->snapshot;
        $tgl = $d->tanggal;
        $totalSen = Uang::sen($spj->total);

        $baris = [];
        foreach ($spj->items as $i => $it) {
            $baris[] = [
                'no' => $i + 1, 'uraian' => $it->uraian, 'volume' => Uang::isianVolume($it->volume), 'satuan' => $it->satuan,
                'harga' => Uang::isianHarga($it->harga_satuan), 'jumlah' => Uang::isianHarga($it->jumlah),
            ];
        }

        // Honor/insentif: satu item = satu penerima. Diterima = honor - pajak.
        $penerima = [];
        $totalPajak = 0;
        foreach ($spj->items as $i => $it) {
            $honor = Uang::sen($it->jumlah);
            $pajak = Uang::sen($it->pajak);
            $totalPajak += $pajak;
            $penerima[] = [
                'no' => $i + 1, 'nama' => $it->uraian, 'jabatan' => $it->jabatan, 'honor' => Uang::isianHarga($it->jumlah),
                'bank' => $it->nama_bank, 'rekening' => $it->no_rekening,
                'pajak' => Uang::isianHarga($it->pajak), 'diterima' => Uang::isianHarga(Uang::format($honor - $pajak)),
            ];
        }
        $totalDiterima = $totalSen - $totalPajak;

        return [
            'penerima' => $penerima,
            'total_pajak' => Uang::isianHarga(Uang::format($totalPajak)),
            'total_diterima' => Uang::isianHarga(Uang::format($totalDiterima)),
            'terbilang_diterima' => Terbilang::rupiah($totalDiterima),
            'd' => $d, 's' => $s, 'induk' => $d->induk,
            'nomor' => $d->nomor, 'items' => $baris,
            'total' => Uang::isianHarga($spj->total), 'total_rp' => 'Rp ' . Uang::isianHarga($spj->total),
            'terbilang' => Terbilang::rupiah($totalSen),
            'tanggal_panjang' => TanggalIndo::panjang($tgl), 'tanggal_kata' => TanggalIndo::kata($tgl),
            'uraian' => $spj->uraian,
            // 'logo' => $this->logoDataUri($s['opd']['logo_path'] ?? null),
            'logo' => $this->logoDataUri($spj->opd?->logo_path ?: ($s['opd']['logo_path'] ?? null)),
            'titik' => '..............................',
        ];
    }

    private function snapshot(Spj $spj): array
    {
        $spj->loadMissing(['opd', 'penyedia', 'sumberDana', 'anggaranRekening.rekening', 'anggaranRekening.subKegiatan.kegiatan.program', 'anggaranRekening.dpaVersi']);
        $ar = $spj->anggaranRekening;
        $sub = $ar->subKegiatan;

        $pejabat = [];
        foreach ([
            'pptk' => 'pptk_id', 'bendahara' => 'bendahara_id', 'pembuat_komitmen' => 'pembuat_komitmen_id',
            'pejabat_pengadaan' => 'pejabat_pengadaan_id', 'pengurus_barang' => 'pengurus_barang_id', 'pengguna_anggaran' => 'pengguna_anggaran_id',
        ] as $kunci => $kolom) {
            $p = $spj->{$kolom} ? PejabatOpd::withoutGlobalScopes()->find($spj->{$kolom}) : null;
            $pejabat[$kunci] = $p ? [
                'nama' => $p->nama, 'nip' => Nip::format($p->nip), 'jabatan' => $p->jabatan, 'jabatan_struktural' => $p->jabatan_struktural,
            ] : ['nama' => '', 'nip' => '', 'jabatan' => '', 'jabatan_struktural' => null];
        }

        $sk = [];
        foreach (self::SK as $kunci => $jenis) {
            $r = SkPenunjukan::withoutGlobalScopes()->where('opd_id', $spj->opd_id)->where('jenis', $jenis)
                ->where('tahun', $spj->tahun)->orderByDesc('tanggal')->first();
            $sk[$kunci] = $r ? ['nomor' => $r->nomor, 'tanggal' => TanggalIndo::panjang($r->tanggal), 'penerbit' => $r->penerbit] : null;
        }

        return [
            'kategori' => $spj->kategori ?? 'barang_jasa',
            'pemda' => config('spj.pemda'), 'kota' => config('spj.kota'), 'dasar_hukum' => config('spj.dasar_hukum_pengadaan'),
            'opd' => ['nama' => $spj->opd->nama, 'singkatan' => $spj->opd->singkatan, 'alamat' => $spj->opd->alamat, 'logo_path' => $spj->opd->logo_path],
            'pejabat' => $pejabat,
            'penyedia' => ['nama' => $spj->penyedia?->nama ?? '', 'alamat' => $spj->penyedia?->alamat ?? ''],
            'sk' => $sk,
            'anggaran' => [
                'tahun' => $spj->tahun, 'sumber_dana' => $spj->sumberDana?->kode, 'nomor_dpa' => $ar->dpaVersi?->nomor_dpa,
                'program' => $sub->kegiatan->program->uraian, 'kegiatan' => $sub->kegiatan->uraian, 'sub_kegiatan' => $sub->uraian,
                'rekening_kode' => $ar->rekening->kode, 'rekening_nama' => $ar->rekening->nama,
            ],
        ];
    }

    /** Logo disematkan sebagai data URI (dompdf hanya andal membaca PNG dan JPG). */
    private function logoDataUri(?string $path): ?string
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }
        $mime = Storage::disk('public')->mimeType($path);
        if (! in_array($mime, ['image/png', 'image/jpeg'], true)) {
            return null;
        }

        return 'data:' . $mime . ';base64,' . base64_encode(Storage::disk('public')->get($path));
    }
}