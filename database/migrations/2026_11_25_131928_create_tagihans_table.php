<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihans', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('opd_id');
            $table->unsignedBigInteger('paket_id');
            
            $table->string('no_invoice')->unique();
            $table->decimal('total_bayar', 15, 2);
            $table->date('periode_mulai');
            $table->date('periode_berakhir');
            $table->enum('status', ['unpaid', 'paid', 'expired'])->default('unpaid');
            $table->string('bukti_transfer')->nullable();
            $table->timestamps();

            // --- UBAH BAGIAN INI ---
            // Ganti 'opds' menjadi 'opd' sesuai nama tabel di database-mu
            $table->foreign('opd_id')->references('id')->on('opd')->onDelete('cascade');
            $table->foreign('paket_id')->references('id')->on('pakets')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihans');
    }
};