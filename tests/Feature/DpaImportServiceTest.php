<?php

namespace Tests\Feature;

use App\Exceptions\DpaImportException;
use App\Models\AnggaranRekening;
use App\Models\DpaVersi;
use App\Models\Opd;
use App\Services\DpaImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\DpaFixture;
use Tests\TestCase;

class DpaImportServiceTest extends TestCase
{
    use RefreshDatabase;

    private Opd $opd;
    private DpaImportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->opd = Opd::create(['kode' => DpaFixture::UNIT_KODE, 'nama' => 'Dinas Pemadam Kebakaran dan Penyelamatan', 'singkatan' => 'DAMKAR']);
        $this->service = app(DpaImportService::class);
    }

    private function stage(array $rows, ?Opd $opd = null)
    {
        return $this->service->stageRows($opd ?? $this->opd, $rows, 'dpa.xlsx', ['tahun' => 2026, 'nomor_dpa' => 'DPA/TEST/2026']);
    }

    private function rekening(DpaVersi $versi, string $kodeSub, string $kodeRek): AnggaranRekening
    {
        return AnggaranRekening::where('dpa_versi_id', $versi->id)
            ->whereHas('subKegiatan', fn ($q) => $q->where('kode', $kodeSub))
            ->whereHas('rekening', fn ($q) => $q->where('kode', $kodeRek))
            ->firstOrFail();
    }

    public function test_stage_gagal_bila_kode_unit_tidak_sama_dengan_opd(): void
    {
        $lain = Opd::create(['kode' => '7.01.01.2.09.0002', 'nama' => 'Kecamatan Tawaeli', 'singkatan' => 'TAWAELI']);

        $batch = $this->stage(DpaFixture::rows(), $lain);

        $this->assertSame('gagal', $batch->status);
        $this->assertStringContainsString('tidak sama dengan kode OPD Anda', $batch->catatan[0]);
    }

    public function test_stage_siap_menyimpan_ringkasan_dan_baris_staging(): void
    {
        $batch = $this->stage(DpaFixture::rows());

        $this->assertSame('siap', $batch->status);
        $this->assertSame(0, $batch->jumlah_error);
        $this->assertSame(2, $batch->jumlah_sub_kegiatan);
        $this->assertSame(4, $batch->jumlah_rekening);
        $this->assertSame('3835500.00', $batch->total_pagu);
        $this->assertSame(8, $batch->rows()->count());
    }

    public function test_commit_membuat_hierarki_dpa_dan_versi_aktif(): void
    {
        $versi = $this->service->commit($this->stage(DpaFixture::rows()));

        $this->assertTrue($versi->aktif);
        $this->assertSame(1, $versi->nomor_versi);
        $this->assertSame('DPA/TEST/2026', $versi->nomor_dpa);
        $this->assertDatabaseCount('program', 1);
        $this->assertDatabaseCount('kegiatan', 1);
        $this->assertDatabaseCount('sub_kegiatan', 2);
        $this->assertDatabaseCount('anggaran_rekening', 4);
        $this->assertDatabaseCount('rekening', 2); // 2 kode rekening unik dibuat di master

        $ar = $this->rekening($versi, '1.05.01.2.01.0001', '5.1.02.02.001.00071');
        $this->assertSame('1500000.00', $ar->pagu);
        $this->assertSame('1500000.00', $ar->sisa);
    }

    public function test_batch_gagal_tidak_bisa_dikomit(): void
    {
        $batch = $this->stage(DpaFixture::rows(310500, 1900000)); // pagu sub tidak cocok

        $this->assertSame('gagal', $batch->status);
        $this->expectException(DpaImportException::class);
        $this->service->commit($batch);
    }

    public function test_batch_tidak_bisa_dikomit_dua_kali(): void
    {
        $batch = $this->stage(DpaFixture::rows());
        $this->service->commit($batch);

        $this->expectException(DpaImportException::class);
        $this->service->commit($batch);
    }

    public function test_versi_baru_menonaktifkan_versi_lama_dan_membawa_realisasi(): void
    {
        $v1 = $this->service->commit($this->stage(DpaFixture::rows()));
        $this->rekening($v1, '1.05.01.2.01.0001', '5.1.02.01.001.00026')
            ->update(['realisasi_final' => 100000, 'reserved' => 50000]);

        $v2 = $this->service->commit($this->stage(DpaFixture::rows(400000))); // pagu cetak naik

        $this->assertNull($v1->fresh()->aktif);
        $this->assertTrue($v2->aktif);
        $this->assertSame(2, $v2->nomor_versi);

        $baru = $this->rekening($v2, '1.05.01.2.01.0001', '5.1.02.01.001.00026');
        $this->assertSame('400000.00', $baru->pagu);
        $this->assertSame('100000.00', $baru->realisasi_final);
        $this->assertSame('50000.00', $baru->reserved);
        $this->assertSame('250000.00', $baru->sisa);
    }

    public function test_commit_ditolak_bila_pagu_baru_di_bawah_realisasi_dan_reserved(): void
    {
        $v1 = $this->service->commit($this->stage(DpaFixture::rows()));
        $this->rekening($v1, '1.05.01.2.01.0001', '5.1.02.01.001.00026')
            ->update(['realisasi_final' => 300000, 'reserved' => 20000]); // total 320.000

        $batch2 = $this->stage(DpaFixture::rows(310000)); // pagu baru 310.000 < 320.000

        try {
            $this->service->commit($batch2);
            $this->fail('Seharusnya DpaImportException dilempar.');
        } catch (DpaImportException $e) {
            $this->assertStringContainsString('lebih kecil dari realisasi', $e->getMessage());
        }

        // transaksi di-rollback: versi lama tetap aktif, tidak ada versi baru, batch masih siap
        $this->assertSame(1, DpaVersi::count());
        $this->assertTrue($v1->fresh()->aktif);
        $this->assertSame('siap', $batch2->fresh()->status);
    }
}