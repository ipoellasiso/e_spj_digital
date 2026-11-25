<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Untuk SPJ honor/insentif, satu item = satu penerima. Kolom jumlah = honor (bruto),
 * pajak = nilai pajak yang dipotong, sehingga diterima = jumlah - pajak.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spj_item', function (Blueprint $t) {
            $t->string('jabatan')->nullable()->after('uraian');
            $t->string('nama_bank', 100)->nullable()->after('jumlah');
            $t->string('no_rekening', 50)->nullable()->after('nama_bank');
            $t->decimal('pajak', 18, 2)->default(0)->after('no_rekening');
        });
    }

    public function down(): void
    {
        Schema::table('spj_item', function (Blueprint $t) {
            $t->dropColumn(['jabatan', 'nama_bank', 'no_rekening', 'pajak']);
        });
    }
};