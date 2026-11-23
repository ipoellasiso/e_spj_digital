<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dipakai di route tanpa perlu alias di Kernel:
 *   ->middleware(PeranDiizinkan::class . ':superadmin,admin')
 */
class PeranDiizinkan
{
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        $user = $request->user();
        abort_unless($user && $user->hasAnyRole($peran), 403, 'Anda tidak memiliki akses ke halaman ini.');

        return $next($request);
    }
}