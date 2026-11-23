<?php

namespace App\Http\Controllers;

use App\Enums\SpjStatus;
use App\Models\Spj;
use App\Models\AnggaranRekening;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VerifikasiController extends Controller
{
    // Halaman daftar SPJ yang butuh verifikasi user ini
    public function index(Request $request): View
    {
        $user = $request->user();
        
        $query = Spj::with(['pembuat', 'anggaranRekening.rekening'])
            ->where('status', '!=', SpjStatus::Draft)
            ->where('status', '!=', SpjStatus::Final)
            ->where('status', '!=', SpjStatus::Dikembalikan);

        // Filter berdasarkan role yang login
        if ($user->hasRole('bendahara')) {
            $query->where('status', SpjStatus::Diajukan);
        } elseif ($user->hasRole('ppk_skpd')) {
            $query->where('status', SpjStatus::DiverifikasiBendahara);
        } elseif ($user->hasRole('pa')) {
            $query->where('status', SpjStatus::DiverifikasiPpk);
        }

        return view('verifikasi.index', [
            'daftar_spj' => $query->latest()->get(),
            'judul' => 'Antrian Verifikasi & Persetujuan'
        ]);
    }

    // Proses Setuju / Tolak
    public function proses(Request $request, Spj $spj)
    {
        $user = $request->user();
        $aksi = $request->input('aksi'); 
        $catatan = $request->input('catatan');

        if ($aksi === 'tolak' && empty($catatan)) {
            return back()->withErrors(['catatan' => 'Catatan wajib diisi jika menolak SPJ.']);
        }

        DB::transaction(function () use ($spj, $user, $aksi, $catatan) {
            $statusLama = $spj->status;
            $statusBaru = $statusLama;
            $updatePagu = false;

            // Tentukan status baru berdasarkan role & aksi
            if ($aksi === 'tolak') {
                $statusBaru = SpjStatus::Dikembalikan;
                $updatePagu = true;
            } else {
                if ($user->hasRole('bendahara')) $statusBaru = SpjStatus::DiverifikasiBendahara;
                elseif ($user->hasRole('ppk_skpd')) $statusBaru = SpjStatus::DiverifikasiPpk;
                elseif ($user->hasRole('pa')) {
                    $statusBaru = SpjStatus::Final;
                    $updatePagu = true;
                }
            }

            // Update Pagu
            if ($updatePagu && $spj->anggaranRekening) {
                $ar = $spj->anggaranRekening;
                $nilaiSpj = $spj->total;

                if ($statusBaru === SpjStatus::Dikembalikan) {
                    $ar->decrement('reserved', $nilaiSpj);
                } elseif ($statusBaru === SpjStatus::Final) {
                    $ar->decrement('reserved', $nilaiSpj);
                    $ar->increment('realisasi_final', $nilaiSpj);
                }
            }

            // Tentukan tahap
            $tahap = '';
            if ($user->hasRole('bendahara')) $tahap = 'Bendahara';
            elseif ($user->hasRole('ppk_skpd')) $tahap = 'PPK-SKPD';
            elseif ($user->hasRole('pa')) $tahap = 'PA';

            // Catat Riwayat
            $spj->approval()->create([
                'user_id' => $user->id,
                'tahap' => $tahap,
                'aksi' => $aksi === 'setuju' ? 'Disetujui' : 'Ditolak',
                'catatan' => $catatan,
            ]);

            // SIMPAN STATUS
            $spj->update(['status' => $statusBaru]);
        });

        return redirect()->route('spj.show', $spj)->with('sukses', "SPJ berhasil di{$aksi}.");
    }
}