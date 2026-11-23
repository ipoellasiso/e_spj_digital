<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfilController extends Controller
{
    public function formPassword(): View
    {
        return view('profil.password');
    }

    public function gantiPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password_lama' => ['required', 'current_password'],
            'password_baru' => ['required', 'confirmed', Password::min(8)],
        ], [
            'password_lama.current_password' => 'Kata sandi lama tidak cocok.',
            'password_baru.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password_baru.min' => 'Kata sandi baru minimal 8 karakter.',
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password_baru),
        ]);

        return back()->with('sukses', 'Kata sandi berhasil diperbarui.');
    }
}