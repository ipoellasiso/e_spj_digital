<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Opd;
use Illuminate\Http\Request;

/** Data master per OPD: superadmin memilih OPD di form, user lain otomatis memakai OPD-nya. */
trait PilihOpd
{
    protected function aturanOpd(Request $request): array
    {
        return $request->user()->isSuperadmin() ? ['opd_id' => ['required', 'exists:opd,id']] : [];
    }

    protected function opdUntukSimpan(Request $request, array $data): int
    {
        $me = $request->user();
        if ($me->isSuperadmin()) {
            return (int) $data['opd_id'];
        }
        abort_unless($me->opd_id, 403, 'Akun Anda belum terhubung ke OPD.');

        return (int) $me->opd_id;
    }

    protected function daftarOpd(Request $request)
    {
        return $request->user()->isSuperadmin() ? Opd::orderBy('nama')->get(['id', 'nama']) : collect();
    }
}