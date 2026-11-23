<?php

namespace App\View\Composers;

use App\Models\DpaVersi;
use App\Services\SpjWorkflowService;
use Illuminate\View\View;

/** Menyediakan $ctx (OPD, tahun anggaran, versi DPA aktif, peran, inisial) untuk layouts.app. */
class LayoutComposer
{
    private const PERAN = [
        'superadmin' => 'Superadmin',
        'admin' => 'Admin OPD',
        'bendahara' => 'Bendahara pengeluaran',
        'pptk' => 'PPTK',
        'ppk_skpd' => 'PPK-SKPD',
        'pa' => 'Pengguna anggaran',
    ];

    public function compose(View $view): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $tahun = (int) session('tahun_anggaran', now()->year);

        $dpa = 'DPA belum diimpor';
        if ($user->opd_id) {
            $versi = DpaVersi::withoutGlobalScopes()
                ->where('opd_id', $user->opd_id)->where('tahun', $tahun)->where('aktif', true)
                ->first();
            if ($versi) {
                $dpa = 'DPA ' . $versi->jenis . ' (versi ' . $versi->nomor_versi . ')';
            }
        }

        $kode = $user->getRoleNames()->first();
        $kata = preg_split('/\s+/', trim($user->name)) ?: [];
        $inisial = mb_strtoupper(mb_substr($kata[0] ?? '?', 0, 1) . (isset($kata[1]) ? mb_substr($kata[1], 0, 1) : ''));

        $view->with('ctx', (object) [
            'opd' => $user->opd?->nama ?? 'Semua OPD',
            'tahun' => $tahun,
            'dpa' => $dpa,
            'peran' => self::PERAN[$kode] ?? 'Pengguna',
            'inisial' => $inisial,
            // jumlah SPJ yang menunggu tindakan pengguna ini (untuk lencana menu)
            'menunggu' => app(SpjWorkflowService::class)->jumlahMenunggu($user),
        ]);
    }
}