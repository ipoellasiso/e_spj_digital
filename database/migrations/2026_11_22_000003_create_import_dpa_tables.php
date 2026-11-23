<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tahap 2: tabel staging impor DPA (data baru masuk DPA setelah dikonfirmasi). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_dpa_batch', function (Blueprint $t) {
            $t->id();
            $t->foreignId('opd_id')->constrained('opd')->cascadeOnDelete();
            $t->unsignedSmallInteger('tahun');
            $t->string('jenis', 15)->default('murni');
            $t->string('nomor_dpa')->nullable();
            $t->string('nama_file');
            $t->string('unit_kode', 40)->nullable();
            $t->string('unit_nama')->nullable();
            $t->string('status', 12);                           // gagal | siap | dikomit
            $t->unsignedInteger('jumlah_program')->default(0);
            $t->unsignedInteger('jumlah_kegiatan')->default(0);
            $t->unsignedInteger('jumlah_sub_kegiatan')->default(0);
            $t->unsignedInteger('jumlah_rekening')->default(0);
            $t->decimal('total_pagu', 18, 2)->default(0);        // jumlah pagu seluruh sub kegiatan
            $t->unsignedInteger('jumlah_error')->default(0);
            $t->json('catatan')->nullable();                     // galat tingkat file (mis. kode unit tidak cocok)
            $t->foreignId('diunggah_oleh')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('dpa_versi_id')->nullable()->constrained('dpa_versi')->nullOnDelete();
            $t->timestamps();
            $t->index(['opd_id', 'status']);
        });

        Schema::create('import_dpa_row', function (Blueprint $t) {
            $t->id();
            $t->foreignId('batch_id')->constrained('import_dpa_batch')->cascadeOnDelete();
            $t->unsignedInteger('baris');                        // nomor baris di Excel
            $t->string('level', 15);                             // program | kegiatan | sub_kegiatan | rekening
            $t->string('kode', 40);
            $t->string('induk_kode', 40)->nullable();
            $t->string('uraian', 500);
            $t->decimal('pagu', 18, 2)->nullable();
            $t->string('pesan_error')->nullable();
            $t->index(['batch_id', 'baris']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_dpa_row');
        Schema::dropIfExists('import_dpa_batch');
    }
};