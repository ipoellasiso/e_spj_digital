<?php

namespace App\Services;

use App\Models\DpaVersi;
use App\Models\Opd;
use App\Models\PejabatOpd;
use App\Models\Spj;
use App\Support\Nip;
use App\Support\Uang;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Data laporan. Semua nilai uang dihitung dalam sen (integer).
 * Realisasi = SPJ berstatus final. "Dalam proses" = SPJ yang sudah diajukan tetapi belum final.
 */
class LaporanService
{
    private const STATUS_PROSES = ['diajukan', 'diverifikasi_bendahara', 'diverifikasi_ppk'];

    // ------------------------------------------------------------------ BKU

    /**
     * SPJ final pada periode, urut tanggal lalu nomor. $batas = kode sub kegiatan yang boleh dilihat (null = semua).
     */
    public function bkuQuery(Opd $opd, int $tahun, CarbonInterface $dari, CarbonInterface $sampai, ?array $batas): Builder
    {
        return Spj::query()
            ->with(['anggaranRekening.rekening:id,kode,nama', 'sumberDana:id,kode'])
            ->where('spj.opd_id', $opd->id)->where('spj.tahun', $tahun)->where('spj.status', 'final')
            ->whereBetween('spj.tanggal', [$dari->toDateString(), $sampai->toDateString()])
            ->when($batas !== null, fn ($q) => $q->whereHas('anggaranRekening.subKegiatan', fn ($s) => $s->whereIn('kode', $batas)));
    }

    /** @return array{jumlah: int, total_sen: int} */
    public function bkuRingkasan(Builder $query): array
    {
        $q = (clone $query)->reorder();

        return ['jumlah' => (clone $q)->count(), 'total_sen' => Uang::sen((clone $q)->sum('spj.total'))];
    }

    /** Semua baris BKU untuk ekspor. */
    public function bkuBaris(Builder $query): array
    {
        $baris = [];
        foreach ((clone $query)->orderBy('spj.tanggal')->orderBy('spj.nomor_urut')->get() as $i => $s) {
            $rek = $s->anggaranRekening?->rekening;
            $baris[] = [
                'no' => $i + 1,
                'tanggal' => $s->tanggal->format('d/m/Y'),
                'nomor' => $s->nomor ?? '-',
                'uraian' => $s->uraian,
                'rekening' => $rek ? $rek->kode . ' ' . $rek->nama : '-',
                'sumber_dana' => $s->sumberDana?->kode ?? '-',
                'jumlah_sen' => Uang::sen($s->total),
            ];
        }

        return $baris;
    }

    // ------------------------------------------------------------ Realisasi

    /**
     * Pagu dan realisasi per program, kegiatan, sub kegiatan, dan rekening belanja pada DPA aktif.
     * Realisasi dihitung dari SPJ bertanggal sampai $sampai.
     *
     * @return array{versi: ?DpaVersi, baris: array<int, array>, total: array}
     */
    public function realisasi(Opd $opd, int $tahun, CarbonInterface $sampai, ?array $batas): array
    {
        $versi = DpaVersi::withoutGlobalScopes()->where('opd_id', $opd->id)->where('tahun', $tahun)->where('aktif', true)->first();
        $kosong = ['versi' => $versi, 'baris' => [], 'total' => $this->hitungTotal(0, 0, 0)];
        if (! $versi) {
            return $kosong;
        }

        $rekening = DB::table('anggaran_rekening as ar')
            ->join('rekening as r', 'r.id', '=', 'ar.rekening_id')
            ->join('sub_kegiatan as sk', 'sk.id', '=', 'ar.sub_kegiatan_id')
            ->join('kegiatan as k', 'k.id', '=', 'sk.kegiatan_id')
            ->join('program as p', 'p.id', '=', 'k.program_id')
            ->where('ar.dpa_versi_id', $versi->id)
            ->when($batas !== null, fn ($q) => $q->whereIn('sk.kode', $batas))
            ->orderBy('p.kode')->orderBy('k.kode')->orderBy('sk.kode')->orderBy('r.kode')
            ->get([
                'ar.id as ar_id', 'ar.pagu', 'r.kode as r_kode', 'r.nama as r_nama',
                'sk.kode as sk_kode', 'sk.uraian as sk_uraian', 'k.kode as k_kode', 'k.uraian as k_uraian', 'p.kode as p_kode', 'p.uraian as p_uraian',
            ]);

        $proses = "'" . implode("','", self::STATUS_PROSES) . "'";
        $realisasi = DB::table('spj')
            ->where('opd_id', $opd->id)->where('tahun', $tahun)->whereDate('tanggal', '<=', $sampai->toDateString())
            ->groupBy('anggaran_rekening_id')
            ->selectRaw("anggaran_rekening_id, SUM(CASE WHEN status = 'final' THEN total ELSE 0 END) AS jml_final, SUM(CASE WHEN status IN ({$proses}) THEN total ELSE 0 END) AS jml_proses")
            ->get()->keyBy('anggaran_rekening_id');

        $nilaiRekening = [];
        foreach ($rekening as $r) {
            $rl = $realisasi[$r->ar_id] ?? null;
            $nilaiRekening[$r->ar_id] = [Uang::sen($r->pagu), Uang::sen($rl->jml_final ?? 0), Uang::sen($rl->jml_proses ?? 0)];
        }
        [$baris, $total] = $this->susun($rekening, $nilaiRekening);

        return ['versi' => $versi, 'baris' => $baris, 'total' => $this->hitungTotal(...$total)];
    }

    /** Penandatangan laporan: pengguna anggaran dan bendahara pengeluaran yang sedang menjabat. */
    public function penandatangan(Opd $opd): array
    {
        $hariIni = now()->toDateString();
        $ambil = function (array $jabatan) use ($opd, $hariIni) {
            $p = PejabatOpd::withoutGlobalScopes()->where('opd_id', $opd->id)->whereIn('jabatan', $jabatan)
                ->where(fn ($w) => $w->whereNull('aktif_dari')->orWhere('aktif_dari', '<=', $hariIni))
                ->where(fn ($w) => $w->whereNull('aktif_sampai')->orWhere('aktif_sampai', '>=', $hariIni))
                ->orderBy('id')->first();

            return $p ? ['nama' => $p->nama, 'nip' => Nip::format($p->nip)] : ['nama' => '', 'nip' => ''];
        };

        return ['pa' => $ambil(['PA', 'KPA']), 'bendahara' => $ambil(['Bendahara Pengeluaran'])];
    }


    /**
     * Menyusun baris laporan berjenjang dari rekening yang sudah terurut (program, kegiatan, sub kegiatan, rekening).
     * Jumlah tiap jenjang adalah penjumlahan rekening di bawahnya.
     *
     * @param  iterable<object>  $rekening  objek dengan ar_id, r_kode, r_nama, sk_kode, sk_uraian, k_kode, k_uraian, p_kode, p_uraian
     * @param  array<int, array{0:int,1:int,2:int}>  $nilai  ar_id => [pagu, final, proses] dalam sen
     * @return array{0: array<int, array>, 1: array{0:int,1:int,2:int}} baris dan total keseluruhan
     */
    public function susun(iterable $rekening, array $nilai): array
    {
        $pohon = [];
        $total = [0, 0, 0];
        foreach ($rekening as $r) {
            $n = $nilai[$r->ar_id] ?? [0, 0, 0];
            $this->tambah($total, $n);

            $pohon[$r->p_kode]['uraian'] = $r->p_uraian;
            $this->tambah($pohon[$r->p_kode]['jumlah'], $n);

            $pohon[$r->p_kode]['kegiatan'][$r->k_kode]['uraian'] = $r->k_uraian;
            $this->tambah($pohon[$r->p_kode]['kegiatan'][$r->k_kode]['jumlah'], $n);

            $pohon[$r->p_kode]['kegiatan'][$r->k_kode]['sub'][$r->sk_kode]['uraian'] = $r->sk_uraian;
            $this->tambah($pohon[$r->p_kode]['kegiatan'][$r->k_kode]['sub'][$r->sk_kode]['jumlah'], $n);

            $pohon[$r->p_kode]['kegiatan'][$r->k_kode]['sub'][$r->sk_kode]['rekening'][] = ['kode' => $r->r_kode, 'uraian' => $r->r_nama, 'nilai' => $n];
        }

        $baris = [];
        foreach ($pohon as $kodeP => $p) {
            $baris[] = $this->baris('program', (string) $kodeP, $p['uraian'], $p['jumlah'], null);
            foreach ($p['kegiatan'] as $kodeK => $k) {
                $baris[] = $this->baris('kegiatan', (string) $kodeK, $k['uraian'], $k['jumlah'], null);
                foreach ($k['sub'] as $kodeS => $sub) {
                    $baris[] = $this->baris('sub', (string) $kodeS, $sub['uraian'], $sub['jumlah'], null);
                    foreach ($sub['rekening'] as $rek) {
                        $baris[] = $this->baris('rekening', $rek['kode'], $rek['uraian'], $rek['nilai'], (string) $kodeS);
                    }
                }
            }
        }

        return [$baris, $total];
    }

    /** @param  array{0:int,1:int,2:int}|null  $jumlah */
    private function tambah(?array &$jumlah, array $nilai): void
    {
        $jumlah ??= [0, 0, 0];
        foreach ($nilai as $i => $v) {
            $jumlah[$i] += $v;
        }
    }

    // ------------------------------------------------------------- internal

    private function baris(string $level, string $kode, string $uraian, array $nilai, ?string $induk): array
    {
        [$pagu, $final, $proses] = $nilai;

        return ['level' => $level, 'kode' => $kode, 'uraian' => $uraian, 'induk' => $induk] + $this->hitungTotal($pagu, $final, $proses);
    }

    /** @return array{pagu: int, final: int, proses: int, sisa: int, persen: float} */
    private function hitungTotal(int $pagu, int $final, int $proses): array
    {
        return [
            'pagu' => $pagu, 'final' => $final, 'proses' => $proses, 'sisa' => $pagu - $final - $proses,
            'persen' => $pagu > 0 ? round($final / $pagu * 100, 2) : 0.0,
        ];
    }

    public function realisasiPerSumberDana(Opd $opd, int $tahun, CarbonInterface $sampai, ?array $batas): array
    {
        $versi = DpaVersi::withoutGlobalScopes()->where('opd_id', $opd->id)->where('tahun', $tahun)->where('aktif', true)->first();
        if (! $versi) {
            return ['versi' => null, 'sumber_dana' => [], 'baris' => [], 'total' => []];
        }

        // 1. Ambil semua Sumber Dana yang aktif
        $sumberDana = DB::table('sumber_dana')->orderBy('kode')->get(['id', 'kode', 'nama']);

        // 2. Ambil Data Pagu (Hierarki Program -> Kegiatan -> Sub Kegiatan -> Rekening)
        $rekening = DB::table('anggaran_rekening as ar')
            ->join('rekening as r', 'r.id', '=', 'ar.rekening_id')
            ->join('sub_kegiatan as sk', 'sk.id', '=', 'ar.sub_kegiatan_id')
            ->join('kegiatan as k', 'k.id', '=', 'sk.kegiatan_id')
            ->join('program as p', 'p.id', '=', 'k.program_id')
            ->where('ar.dpa_versi_id', $versi->id)
            ->when($batas !== null, fn ($q) => $q->whereIn('sk.kode', $batas))
            ->orderBy('p.kode')->orderBy('k.kode')->orderBy('sk.kode')->orderBy('r.kode')
            ->get([
                'ar.id as ar_id', 'ar.pagu', 'r.kode as r_kode', 'r.nama as r_nama',
                'sk.kode as sk_kode', 'sk.uraian as sk_uraian', 'k.kode as k_kode', 'k.uraian as k_uraian', 'p.kode as p_kode', 'p.uraian as p_uraian',
            ]);

        // 3. Ambil Data Realisasi SPJ yang dikelompokkan berdasarkan Rekening dan Sumber Dana
        $proses = "'" . implode("','", self::STATUS_PROSES) . "'";
        $realisasiRaw = DB::table('spj')
            ->where('opd_id', $opd->id)->where('tahun', $tahun)->whereDate('tanggal', '<=', $sampai->toDateString())
            ->groupBy('anggaran_rekening_id', 'sumber_dana_id')
            ->selectRaw("anggaran_rekening_id, sumber_dana_id, 
                SUM(CASE WHEN status = 'final' THEN total ELSE 0 END) AS jml_final, 
                SUM(CASE WHEN status IN ({$proses}) THEN total ELSE 0 END) AS jml_proses")
            ->get();

        // Petakan realisasi: [ar_id][sd_id] = [final, proses]
        $realisasiMap = [];
        foreach ($realisasiRaw as $row) {
            $realisasiMap[$row->anggaran_rekening_id][$row->sumber_dana_id] = [
                Uang::sen($row->jml_final),
                Uang::sen($row->jml_proses)
            ];
        }

        // 4. Susun Baris Data (Tree Table)
        $pohon = [];
        $totalGlobal = ['pagu' => 0, 'dana' => []]; // dana[sd_id] = [final, proses]

        foreach ($rekening as $r) {
            $paguSen = Uang::sen($r->pagu);
            $totalGlobal['pagu'] += $paguSen;

            // Inisialisasi struktur pohon
            $pohon[$r->p_kode] ??= ['kode' => $r->p_kode, 'uraian' => $r->p_uraian, 'pagu' => 0, 'dana' => [], 'level' => 'program', 'anak' => []];
            $pohon[$r->p_kode]['pagu'] += $paguSen;

            $pohon[$r->p_kode]['anak'][$r->k_kode] ??= ['kode' => $r->k_kode, 'uraian' => $r->k_uraian, 'pagu' => 0, 'dana' => [], 'level' => 'kegiatan', 'anak' => []];
            $pohon[$r->p_kode]['anak'][$r->k_kode]['pagu'] += $paguSen;

            $pohon[$r->p_kode]['anak'][$r->k_kode]['anak'][$r->sk_kode] ??= ['kode' => $r->sk_kode, 'uraian' => $r->sk_uraian, 'pagu' => 0, 'dana' => [], 'level' => 'sub', 'anak' => []];
            $pohon[$r->p_kode]['anak'][$r->k_kode]['anak'][$r->sk_kode]['pagu'] += $paguSen;

            $pohon[$r->p_kode]['anak'][$r->k_kode]['anak'][$r->sk_kode]['anak'][$r->ar_id] = [
                'kode' => $r->r_kode, 'uraian' => $r->r_nama, 'pagu' => $paguSen, 'dana' => $realisasiMap[$r->ar_id] ?? [], 'level' => 'rekening', 'induk' => $r->sk_kode, 'anak' => []
            ];

            // Distribusikan nilai realisasi ke atas (Program, Kegiatan, Sub)
            if (isset($realisasiMap[$r->ar_id])) {
                foreach ($realisasiMap[$r->ar_id] as $sd_id => $nilai) {
                    foreach (['program', 'kegiatan', 'sub'] as $level) {
                        $ref = &$pohon[$r->p_kode];
                        if ($level === 'kegiatan' || $level === 'sub') $ref = &$ref['anak'][$r->k_kode];
                        if ($level === 'sub') $ref = &$ref['anak'][$r->sk_kode];
                        
                        $ref['dana'][$sd_id][0] = ($ref['dana'][$sd_id][0] ?? 0) + $nilai[0];
                        $ref['dana'][$sd_id][1] = ($ref['dana'][$sd_id][1] ?? 0) + $nilai[1];
                        unset($ref);
                    }
                    
                    // Total Global
                    $totalGlobal['dana'][$sd_id][0] = ($totalGlobal['dana'][$sd_id][0] ?? 0) + $nilai[0];
                    $totalGlobal['dana'][$sd_id][1] = ($totalGlobal['dana'][$sd_id][1] ?? 0) + $nilai[1];
                }
            }
        }

        // Ratakan pohon menjadi array baris (Flatten)
        $baris = [];
        foreach ($pohon as $p) {
            $baris[] = $this->formatBaris($p);
            foreach ($p['anak'] as $k) {
                $baris[] = $this->formatBaris($k);
                foreach ($k['anak'] as $sk) {
                    $baris[] = $this->formatBaris($sk);
                    foreach ($sk['anak'] as $rek) {
                        $baris[] = $this->formatBaris($rek);
                    }
                }
            }
        }

        return [
            'versi' => $versi,
            'sumber_dana' => $sumberDana,
            'baris' => $baris,
            'total' => $totalGlobal
        ];
    }

    private function formatBaris(array $item): array
    {
        $final = array_sum(array_column($item['dana'], 0));
        $proses = array_sum(array_column($item['dana'], 1));
        $total = $this->hitungTotal($item['pagu'], $final, $proses);
        
        return [
            'level' => $item['level'],
            'kode' => $item['kode'],
            'uraian' => $item['uraian'],
            'induk' => $item['induk'] ?? null,
            'pagu' => $item['pagu'],
            'dana' => $item['dana'], // format: [sd_id => [final, proses]]
            'persen' => $total['persen']
        ];
    }
}