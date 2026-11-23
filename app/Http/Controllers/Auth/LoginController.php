<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function form(): View
    {
        return view('auth.login');
    }

    /** Masuk dengan email atau NIP. Akun dengan aktif = false ditolak. */
    public function masuk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Isi email atau NIP.',
            'password.required' => 'Isi kata sandi.',
        ]);

        $adalahEmail = filter_var($data['login'], FILTER_VALIDATE_EMAIL) !== false;
        $kolom = $adalahEmail ? 'email' : 'nip';
        // NIP disimpan tanpa spasi; pengguna boleh mengetik dengan spasi ("19841120 201407 2 003").
        $login = $adalahEmail ? $data['login'] : preg_replace('/\s+/', '', $data['login']);

        $berhasil = Auth::attempt(
            [$kolom => $login, 'password' => $data['password'], 'aktif' => true],
            $request->boolean('ingat')
        );

        if (! $berhasil) {
            throw ValidationException::withMessages([
                'login' => 'Email atau NIP dan kata sandi tidak cocok, atau akun tidak aktif.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function keluar(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}