<?php

namespace App\Http\Controllers\Concerns;

use App\Models\DpaVersi;
use App\Models\Opd;
use Illuminate\Http\Request;

/** Menentukan OPD dan tahun yang sedang dilihat. Superadmin memilih lewat ?opd=, user lain terkunci ke OPD-nya. */
trait KonteksOpd
{
    protected function opdKonteks(Request $request): ?Opd
    {
        $me = $request->user();
        if ($me->isSuperadmin()) {
            $id = (int) $request->input('opd');

            return $id > 0 ? Opd::find($id) : null;
        }

        return $me->opd;
    }

    protected function tahunAnggaran(Request $request): int
    {
        return (int) $request->input('tahun') ?: (int) session('tahun_anggaran', now()->year);
    }

    protected function versiAktif(Opd $opd, int $tahun): ?DpaVersi
    {
        return DpaVersi::withoutGlobalScopes()
            ->where('opd_id', $opd->id)->where('tahun', $tahun)->where('aktif', true)->first();
    }
}