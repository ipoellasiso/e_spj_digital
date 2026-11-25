<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Kategori belanja menentukan dokumen cetak dan kelengkapan pengajuan. SPJ lama otomatis menjadi barang_jasa. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spj', function (Blueprint $t) {
            $t->string('kategori', 20)->default('barang_jasa')->after('jenis'); // barang_jasa | honor_lembur | modal | gaji
        });
    }

    public function down(): void
    {
        Schema::table('spj', function (Blueprint $t) {
            $t->dropColumn('kategori');
        });
    }
};