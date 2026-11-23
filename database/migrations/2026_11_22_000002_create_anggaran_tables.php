<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tahap 2: DPA berversi sampai rekening belanja (tanpa item belanja).
 * opd_id di-denormalisasi ke semua tabel anak supaya scope per OPD cukup satu kolom dan cepat.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dpa_versi', function (Blueprint $t) {
            $t->id();
            $t->foreignId('opd_id')->constrained('opd')->restrictOnDelete();
            $t->unsignedSmallInteger('tahun');
            $t->string('jenis', 15)->default('murni');          // murni | perubahan
            $t->unsignedSmallInteger('nomor_versi');
            $t->string('nomor_dpa')->nullable();                // mis. DPA/7.01.01.2.09.0002/001/2026
            // TRUE untuk versi aktif, NULL untuk yang tidak aktif. Unik (opd, tahun, aktif)
            // menjamin hanya ada satu versi aktif per OPD per tahun (NULL boleh berulang).
            $t->boolean('aktif')->nullable();
            $t->foreignId('diimpor_oleh')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->unique(['opd_id', 'tahun', 'nomor_versi']);
            $t->unique(['opd_id', 'tahun', 'aktif']);
        });

        Schema::create('program', function (Blueprint $t) {
            $t->id();
            $t->foreignId('dpa_versi_id')->constrained('dpa_versi')->cascadeOnDelete();
            $t->foreignId('opd_id')->constrained('opd')->restrictOnDelete();
            $t->string('kode', 40);
            $t->string('uraian', 500);
            $t->unique(['dpa_versi_id', 'kode']);
        });

        Schema::create('kegiatan', function (Blueprint $t) {
            $t->id();
            $t->foreignId('dpa_versi_id')->constrained('dpa_versi')->cascadeOnDelete();
            $t->foreignId('program_id')->constrained('program')->cascadeOnDelete();
            $t->foreignId('opd_id')->constrained('opd')->restrictOnDelete();
            $t->string('kode', 40);
            $t->string('uraian', 500);
            $t->unique(['dpa_versi_id', 'kode']);
        });

        Schema::create('sub_kegiatan', function (Blueprint $t) {
            $t->id();
            $t->foreignId('dpa_versi_id')->constrained('dpa_versi')->cascadeOnDelete();
            $t->foreignId('kegiatan_id')->constrained('kegiatan')->cascadeOnDelete();
            $t->foreignId('opd_id')->constrained('opd')->restrictOnDelete();
            $t->string('kode', 40);
            $t->string('uraian', 500);
            $t->decimal('pagu', 18, 2);
            $t->unique(['dpa_versi_id', 'kode']);
            $t->index(['opd_id', 'dpa_versi_id']);
        });

        Schema::create('anggaran_rekening', function (Blueprint $t) {
            $t->id();
            $t->foreignId('dpa_versi_id')->constrained('dpa_versi')->cascadeOnDelete();
            $t->foreignId('sub_kegiatan_id')->constrained('sub_kegiatan')->cascadeOnDelete();
            $t->foreignId('opd_id')->constrained('opd')->restrictOnDelete();
            $t->foreignId('rekening_id')->constrained('rekening')->restrictOnDelete();
            $t->decimal('pagu', 18, 2);
            // Kolom ringkasan agar sisa pagu = 1 baris baca. Diubah HANYA oleh PaguService (tahap 3).
            $t->decimal('realisasi_final', 18, 2)->default(0);
            $t->decimal('reserved', 18, 2)->default(0);
            $t->unique(['sub_kegiatan_id', 'rekening_id']);
            $t->index(['opd_id', 'dpa_versi_id']);
        });

        // Penugasan PPTK disimpan per KODE sub kegiatan (bukan id) supaya tidak hilang saat DPA berganti versi.
        Schema::create('pptk_sub_kegiatan', function (Blueprint $t) {
            $t->id();
            $t->foreignId('opd_id')->constrained('opd')->cascadeOnDelete();
            $t->unsignedSmallInteger('tahun');
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('kode_sub_kegiatan', 40);
            $t->unique(['tahun', 'user_id', 'kode_sub_kegiatan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pptk_sub_kegiatan');
        Schema::dropIfExists('anggaran_rekening');
        Schema::dropIfExists('sub_kegiatan');
        Schema::dropIfExists('kegiatan');
        Schema::dropIfExists('program');
        Schema::dropIfExists('dpa_versi');
    }
};