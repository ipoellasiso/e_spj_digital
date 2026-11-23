<?php

namespace Tests\Feature;

use App\Models\DpaVersi;
use App\Models\Opd;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function user(array $o = []): User
    {
        // password bawaan UserFactory = "password"
        return User::factory()->create($o + ['nip' => '198411202014072003', 'aktif' => true]);
    }

    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $this->get('/beranda')->assertRedirect('/masuk');
    }

    public function test_alamat_akar_mengarah_ke_beranda(): void
    {
        $this->get('/')->assertRedirect('/beranda');
    }

    public function test_halaman_masuk_tampil(): void
    {
        $this->get('/masuk')->assertOk()->assertSee('Email atau NIP');
    }

    public function test_masuk_dengan_email(): void
    {
        $u = $this->user();

        $this->post('/masuk', ['login' => $u->email, 'password' => 'password'])->assertRedirect('/beranda');

        $this->assertAuthenticatedAs($u);
    }

    public function test_masuk_dengan_nip_dengan_atau_tanpa_spasi(): void
    {
        $u = $this->user();

        $this->post('/masuk', ['login' => '19841120 201407 2 003', 'password' => 'password'])->assertRedirect('/beranda');
        $this->assertAuthenticatedAs($u);
    }

    public function test_kata_sandi_salah_ditolak(): void
    {
        $u = $this->user();

        $this->from('/masuk')->post('/masuk', ['login' => $u->email, 'password' => 'salah'])
            ->assertRedirect('/masuk')->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_akun_nonaktif_ditolak(): void
    {
        $u = $this->user(['aktif' => false]);

        $this->post('/masuk', ['login' => $u->email, 'password' => 'password'])->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_beranda_menampilkan_opd_dan_status_dpa(): void
    {
        $opd = Opd::create(['kode' => '7.01.01.2.09.0002', 'nama' => 'Kecamatan Tawaeli', 'singkatan' => 'TAWAELI']);
        $u = $this->user(['opd_id' => $opd->id]);

        $this->actingAs($u)->get('/beranda')->assertOk()
            ->assertSee('Kecamatan Tawaeli')->assertSee('DPA belum diimpor');

        DpaVersi::create(['opd_id' => $opd->id, 'tahun' => now()->year, 'jenis' => 'murni', 'nomor_versi' => 1, 'aktif' => true]);

        $this->actingAs($u)->get('/beranda')->assertSee('DPA murni (versi 1)');
    }

    public function test_keluar(): void
    {
        $this->actingAs($this->user())->post('/keluar')->assertRedirect('/masuk');

        $this->assertGuest();
    }
}