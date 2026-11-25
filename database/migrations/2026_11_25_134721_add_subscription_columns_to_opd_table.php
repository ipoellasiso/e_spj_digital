<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('opd', function (Blueprint $table) {
            $table->foreignId('paket_id')->nullable()->constrained('pakets');
            $table->date('tanggal_berakhir')->nullable();
        });
    }

    public function down()
    {
        Schema::table('opd', function (Blueprint $table) {
            $table->dropForeign(['paket_id']);
            $table->dropColumn(['paket_id', 'tanggal_berakhir']);
        });
    }
};
