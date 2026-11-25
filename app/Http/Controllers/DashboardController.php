<?php

namespace App\Http\Controllers;

use App\Models\AnggaranRekening;
use App\Models\DpaVersi;
use App\Models\Spj; // Pastikan model Spj di-import
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = auth()->user();
        $tahun = now()->year;

        // 1. Ambil DPA Versi Aktif
        $queryDpa = DpaVersi::where('tahun', $tahun)->where('aktif', true);
        if (! $user->isSuperadmin() && $user->opd_id) {
            $queryDpa->where('opd_id', $user->opd_id);
        }
        $dpaAktif = $queryDpa->first();

        // 2. Default Values
        $pagu = 0; $realisasi = 0; $proses = 0; $sisa = 0;
        $persen_final = 0; $persen_proses = 0; $persen_sisa = 0;
        $chart_data = [0,0,0,0,0,0,0,0,0,0,0,0];
        $pagu_tipis = [];

        // 3. Hitung Data
        if ($dpaAktif) {
            $sums = AnggaranRekening::where('dpa_versi_id', $dpaAktif->id)
                ->selectRaw('
                    COALESCE(SUM(pagu),0) AS total_pagu, 
                    COALESCE(SUM(realisasi_final),0) AS total_realisasi, 
                    COALESCE(SUM(reserved),0) AS total_reserved
                ')->first();

            $pagu = (float) $sums->total_pagu;
            $realisasi = (float) $sums->total_realisasi;
            $proses = (float) $sums->total_reserved;
            $sisa = $pagu - $realisasi - $proses;

            if ($pagu > 0) {
                $persen_final = round(($realisasi / $pagu) * 100, 1);
                $persen_proses = round(($proses / $pagu) * 100, 1);
                $persen_sisa = round(($sisa / $pagu) * 100, 1);
            }

            // Simulasi Data Chart
            $p = $persen_final;
            $chart_data = [
                round($p * 0.1, 1), round($p * 0.25, 1), round($p * 0.4, 1), 
                round($p * 0.5, 1), round($p * 0.6, 1), round($p * 0.75, 1), 
                round($p * 0.8, 1), round($p * 0.9, 1), round($p * 0.95, 1), 
                round($p * 0.98, 1), $p, $p
            ];

            // Ambil 4 pagu paling tipis (sisa < 50%)
            $pagu_tipis = AnggaranRekening::where('dpa_versi_id', $dpaAktif->id)
                ->whereRaw('(pagu - realisasi_final - reserved) < (pagu * 0.5)')
                ->orderByRaw('(pagu - realisasi_final - reserved) ASC')
                ->take(4)
                ->get()
                ->map(function($item) {
                    $sisa = $item->pagu - $item->realisasi_final - $item->reserved;
                    $persen = $item->pagu > 0 ? round(($sisa / $item->pagu) * 100) : 0;
                    return [
                        'nama' => $item->rekening->nama ?? 'Rekening',
                        'persen' => $persen
                    ];
                })->toArray();
        }

        // 4. Data SPJ Terbaru
        $spj_terbaru = [
            ['no' => '0263', 'uraian' => 'Service kendaraan pick up', 'kode' => '5.1.02.01.001.00020', 'jumlah' => 1578309, 'status' => 'Final'],
            ['no' => '0264', 'uraian' => 'Belanja alat tulis kantor', 'kode' => '', 'jumlah' => 2340000, 'status' => 'Diajukan'],
            ['no' => '0265', 'uraian' => 'Makan minum rapat koordinasi', 'kode' => '', 'jumlah' => 4750000, 'status' => 'Verifikasi'],
        ];

        // 5. Antrian Tindakan (Dummy)
        $antrian = [
            ['no' => '0264', 'uraian' => 'Belanja alat tulis kantor', 'jumlah' => 'Rp2.340.000', 'kapan' => 'Diajukan 20 Nov'],
            ['no' => '0267', 'uraian' => 'Jasa kebersihan kantor', 'jumlah' => 'Rp3.000.000', 'kapan' => 'Diajukan 20 Nov'],
            ['no' => '0268', 'uraian' => 'Perawatan AC ruang rapat', 'jumlah' => 'Rp1.850.000', 'kapan' => 'Diajukan 19 Nov'],
        ];

        return view('dashboard', compact(
            'pagu', 'realisasi', 'proses', 'sisa', 
            'persen_final', 'persen_proses', 'persen_sisa', 
            'chart_data', 'spj_terbaru', 'pagu_tipis', 'antrian'
        ));
    }
}