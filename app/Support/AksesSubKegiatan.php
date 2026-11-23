<?php

namespace App\Support;

use App\Models\PptkSubKegiatan;
use App\Models\User;

/**
 * Pembatasan sub kegiatan: user yang HANYA berperan PPTK hanya melihat sub kegiatan yang ditugaskan.
 * Peran lain (superadmin, admin, bendahara, PPK-SKPD, PA) melihat semua sub kegiatan OPD-nya.
 */
class AksesSubKegiatan
{
    /** @return string[]|null daftar kode sub kegiatan yang boleh dilihat; null = tanpa batas */
    public static function batasan(User $user, int $tahun): ?array
    {
        if ($user->hasAnyRole(['superadmin', 'admin', 'bendahara', 'ppk_skpd', 'pa']) || ! $user->hasRole('pptk')) {
            return null;
        }

        return PptkSubKegiatan::withoutGlobalScopes()
            ->where('user_id', $user->id)->where('tahun', $tahun)
            ->pluck('kode_sub_kegiatan')->all();
    }
}