<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CekLangganan
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // 1. Superadmin dan Admin Sistem bebas akses (tidak perlu langganan)
        if ($user->hasRole('superadmin')) {
            return $next($request);
        }

        $opd = $user->opd;

        // 2. Jika user tidak terhubung ke OPD (misal akun error), blokir.
        if (!$opd) {
            abort(403, 'Akun Anda tidak terhubung ke OPD manapun.');
        }

        // 3. Cek apakah tanggal berakhir kosong ATAU sudah lewat dari hari ini
        $belumLangganan = is_null($opd->tanggal_berakhir);
        $sudahExpired = $opd->tanggal_berakhir && now()->greaterThan($opd->tanggal_berakhir);

        if ($belumLangganan || $sudahExpired) {
            // Alihkan ke halaman billing/tagihan dengan pesan error
            return redirect()->route('billing.index')
                ->with('error', 'Masa aktif OPD Anda belum dimulai atau sudah berakhir. Silakan selesaikan pembayaran tagihan terlebih dahulu.');
        }

        return $next($request);
    }
}