<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tahap 0-1: OPD, penyesuaian users, pejabat, SK penunjukan, sumber dana, penyedia, rekening.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opd', function (Blueprint $t) {
            $t->id();
            $t->string('kode', 40)->unique();      // mis. 1.05.0.00.0.00.01.0000 (dicocokkan dengan file DPA)
            $t->string('nama');
            $t->string('singkatan', 30);           // dipakai di nomor dokumen: 0263/NP/TAWAELI/Apr/2026
            $t->string('alamat')->nullable();
            $t->string('logo_path')->nullable();
            $t->timestamps();
        });

        Schema::table('users', function (Blueprint $t) {
            $t->foreignId('opd_id')->nullable()->after('id')->constrained('opd')->nullOnDelete();
            $t->string('nip', 30)->nullable()->unique();
            $t->boolean('aktif')->default(true);
        });

        Schema::create('pejabat_opd', function (Blueprint $t) {
            $t->id();
            $t->foreignId('opd_id')->constrained('opd')->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // penandatangan tidak harus punya akun
            $t->string('nama');
            $t->string('nip', 30)->nullable();
            // PA, KPA, PPK-SKPD, Pejabat Pembuat Komitmen, Pejabat Pengadaan, PPTK, Bendahara Pengeluaran, Pengurus Barang
            $t->string('jabatan', 50);
            $t->date('aktif_dari')->nullable();
            $t->date('aktif_sampai')->nullable();
            $t->timestamps();
            $t->index(['opd_id', 'jabatan']);
        });

        Schema::create('sk_penunjukan', function (Blueprint $t) {
            $t->id();
            $t->foreignId('opd_id')->constrained('opd')->cascadeOnDelete();
            $t->string('jenis', 50);               // mis. Penunjukan Pejabat Pengadaan
            $t->string('nomor');
            $t->date('tanggal');
            $t->string('penerbit');                // Walikota / Kepala OPD
            $t->unsignedSmallInteger('tahun');
            $t->timestamps();
            $t->index(['opd_id', 'tahun']);
        });

        Schema::create('sumber_dana', function (Blueprint $t) {
            $t->id();
            $t->string('kode', 20)->unique();
            $t->string('nama');
            $t->timestamps();
        });

        Schema::create('penyedia', function (Blueprint $t) {
            $t->id();
            $t->foreignId('opd_id')->constrained('opd')->cascadeOnDelete();
            $t->string('nama');
            $t->string('alamat')->nullable();
            $t->string('npwp', 30)->nullable();
            $t->string('rekening_bank')->nullable();
            $t->timestamps();
            $t->index(['opd_id', 'nama']);
        });

        Schema::create('rekening', function (Blueprint $t) {
            $t->id();
            $t->string('kode', 40)->unique();      // mis. 5.1.02.01.001.00026
            $t->string('nama', 500);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekening');
        Schema::dropIfExists('penyedia');
        Schema::dropIfExists('sumber_dana');
        Schema::dropIfExists('sk_penunjukan');
        Schema::dropIfExists('pejabat_opd');
        Schema::table('users', function (Blueprint $t) {
            $t->dropConstrainedForeignId('opd_id');
            $t->dropColumn(['nip', 'aktif']);
        });
        Schema::dropIfExists('opd');
    }
};