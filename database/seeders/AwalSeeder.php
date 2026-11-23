<?php

namespace Database\Seeders;

use App\Models\SumberDana;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Data awal: role, sumber dana, dan satu akun superadmin.
 * Aman dijalankan berulang kali (updateOrCreate / firstOrCreate).
 *
 * Akun superadmin dibaca dari .env:
 *   SEED_SUPERADMIN_EMAIL=admin@spj.local
 *   SEED_SUPERADMIN_PASSWORD=...   (kalau kosong, dibuat acak dan ditampilkan SEKALI di terminal)
 */
class AwalSeeder extends Seeder
{
    public const ROLE = ['superadmin', 'admin', 'bendahara', 'pptk', 'ppk_skpd', 'pa'];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::ROLE as $nama) {
            Role::findOrCreate($nama, 'web');
        }

        foreach ([
            'PAD' => 'Pendapatan Asli Daerah',
            'DAU' => 'Dana Alokasi Umum',
            'DAK-FISIK' => 'Dana Alokasi Khusus Fisik',
            'DAK-NONFISIK' => 'Dana Alokasi Khusus Nonfisik',
            'DBH' => 'Dana Bagi Hasil',
            'LAIN' => 'Lain-lain pendapatan yang sah',
        ] as $kode => $nama) {
            SumberDana::firstOrCreate(['kode' => $kode], ['nama' => $nama]);
        }

        // Pakai "?:" (bukan nilai default env) supaya baris kosong di .env (SEED_SUPERADMIN_EMAIL=) tetap memakai nilai bawaan.
        $email = env('SEED_SUPERADMIN_EMAIL') ?: 'admin@spj.local';
        $sandi = env('SEED_SUPERADMIN_PASSWORD') ?: '';
        $acak = $sandi === '';
        if ($acak) {
            $sandi = Str::random(14);
        }

        $user = User::where('email', $email)->first();
        $baru = ! $user;
        if ($baru) {
            $user = new User(['name' => 'Superadmin', 'email' => $email]);
            $user->password = Hash::make($sandi);
            $user->forceFill(['aktif' => true, 'opd_id' => null])->save();
        }
        if (! $user->hasRole('superadmin')) {
            $user->assignRole('superadmin');
        }

        // Kata sandi acak ditampilkan hanya setelah akun DAN role berhasil dibuat.
        if ($baru && $acak) {
            $this->command?->warn("Kata sandi superadmin ($email): $sandi  (catat sekarang, tidak ditampilkan lagi)");
        }
    }
}