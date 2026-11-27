<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pakets', function (Blueprint $table) {
            // Tambahkan kolom harga_tahunan, boleh kosong (nullable) jika tidak semua paket punya opsi tahunan
            $table->decimal('harga_tahunan', 15, 2)->nullable()->after('harga_bulanan');
        });
    }

    public function down(): void
    {
        Schema::table('pakets', function (Blueprint $table) {
            $table->dropColumn('harga_tahunan');
        });
    }
};