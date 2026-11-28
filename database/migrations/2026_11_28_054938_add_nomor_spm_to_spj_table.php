<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('spj', function (Blueprint $table) {
        // Nullable karena draft belum punya SPM
        $table->string('nomor_spm', 100)->nullable()->after('nomor_urut');
    });
}

public function down(): void
{
    Schema::table('spj', function (Blueprint $table) {
        $table->dropColumn('nomor_spm');
    });
}
};
