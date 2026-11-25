<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Opd;
use App\Models\DpaVersi;
use App\Models\Kegiatan;

class LandingPageController extends Controller
{
    public function index(Request $request)
    {
        $tahun = now()->year;
        $opdId = $request->query('opd_id'); // Ambil OPD dari URL

        // Ambil semua OPD untuk isi Dropdown
        $daftarOpd = Opd::orderBy('nama')->get();

        $opdTerpilih = null;
        $dataDpa = null;

        if ($opdId) {
            $opdTerpilih = Opd::find($opdId);

            if ($opdTerpilih) {
                $versi = DpaVersi::where('opd_id', $opdTerpilih->id)
                    ->where('tahun', $tahun)
                    ->where('aktif', true)
                    ->first();

                if ($versi) {
                    // Ambil data kegiatan beserta sub kegiatan dan rekening
                    $kegiatan = Kegiatan::where('dpa_versi_id', $versi->id)
                        ->with(['subKegiatan.anggaranRekening.rekening'])
                        ->get();

                    // Hitung total
                    $totalPagu = 0; $totalFinal = 0; $totalProses = 0;
                    foreach ($kegiatan as $keg) {
                        foreach ($keg->subKegiatan as $sub) {
                            foreach ($sub->anggaranRekening as $rek) {
                                $totalPagu += $rek->pagu;
                                $totalFinal += $rek->realisasi_final;
                                $totalProses += $rek->reserved;
                            }
                        }
                    }

                    $dataDpa = [
                        'versi' => $versi,
                        'kegiatan' => $kegiatan,
                        'total_pagu' => $totalPagu,
                        'total_final' => $totalFinal,
                        'total_proses' => $totalProses,
                        'total_sisa' => $totalPagu - $totalFinal - $totalProses,
                    ];
                }
            }
        }

        return view('landing', compact('daftarOpd', 'opdTerpilih', 'dataDpa', 'tahun'));
    }
}