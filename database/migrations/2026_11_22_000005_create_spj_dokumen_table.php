<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tahap 5: dokumen cetak SPJ (nomor dan snapshot penandatangan) dan jabatan struktural pejabat. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pejabat_opd', function (Blueprint $t) {
            // Jabatan struktural yang tercetak di dokumen, mis. "Kepala Kecamatan Tawaeli". Opsional.
            $t->string('jabatan_struktural')->nullable()->after('jabatan');
        });

        Schema::create('spj_dokumen', function (Blueprint $t) {
            $t->id();
            $t->foreignId('spj_id')->constrained('spj')->cascadeOnDelete();
            $t->string('jenis', 30);                       // nota-pesanan | ba-pengadaan | ba-pemeriksaan | ba-penerimaan | bast | kwitansi
            $t->string('nomor');                           // mis. 0263/NP/TAWAELI/Apr/2026
            $t->date('tanggal');
            $t->foreignId('induk_dokumen_id')->nullable()->constrained('spj_dokumen')->nullOnDelete();
            $t->json('snapshot');                          // pejabat, penyedia, SK, dan data anggaran saat SPJ diajukan
            $t->string('hasil_pemeriksaan', 15)->nullable(); // baik | kurang_baik (hanya BA pemeriksaan)
            $t->timestamps();
            $t->unique(['spj_id', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spj_dokumen');
        Schema::table('pejabat_opd', function (Blueprint $t) {
            $t->dropColumn('jabatan_struktural');
        });
    }
};