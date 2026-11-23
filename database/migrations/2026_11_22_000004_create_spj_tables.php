<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tahap 3: SPJ, item belanja, riwayat persetujuan, dan counter nomor urut. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nomor_counter', function (Blueprint $t) {
            $t->id();
            $t->foreignId('opd_id')->constrained('opd')->cascadeOnDelete();
            $t->unsignedSmallInteger('tahun');
            $t->unsignedInteger('terakhir')->default(0);
            $t->unique(['opd_id', 'tahun']);
        });

        Schema::create('spj', function (Blueprint $t) {
            $t->id();
            $t->foreignId('opd_id')->constrained('opd')->restrictOnDelete();
            $t->unsignedSmallInteger('tahun');
            $t->foreignId('anggaran_rekening_id')->constrained('anggaran_rekening')->restrictOnDelete();
            $t->foreignId('sumber_dana_id')->constrained('sumber_dana')->restrictOnDelete();
            $t->string('jenis', 5)->default('GU');                // UP | GU | TU | LS
            $t->unsignedInteger('nomor_urut')->nullable();        // diberikan saat pertama kali diajukan
            $t->date('tanggal');
            $t->string('uraian', 500);
            $t->foreignId('penyedia_id')->nullable()->constrained('penyedia')->nullOnDelete();
            $t->decimal('total', 18, 2)->default(0);
            $t->string('status', 30)->default('draft');
            $t->foreignId('pptk_id')->nullable()->constrained('pejabat_opd')->nullOnDelete();
            $t->foreignId('bendahara_id')->nullable()->constrained('pejabat_opd')->nullOnDelete();
            $t->foreignId('pembuat_komitmen_id')->nullable()->constrained('pejabat_opd')->nullOnDelete();
            $t->foreignId('pejabat_pengadaan_id')->nullable()->constrained('pejabat_opd')->nullOnDelete();
            $t->foreignId('pengurus_barang_id')->nullable()->constrained('pejabat_opd')->nullOnDelete();
            $t->foreignId('pengguna_anggaran_id')->nullable()->constrained('pejabat_opd')->nullOnDelete();
            $t->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->unique(['opd_id', 'tahun', 'nomor_urut']);        // NULL boleh berulang (draft)
            $t->index(['opd_id', 'tahun', 'status']);
            $t->index(['anggaran_rekening_id', 'status']);
        });

        Schema::create('spj_item', function (Blueprint $t) {
            $t->id();
            $t->foreignId('spj_id')->constrained('spj')->cascadeOnDelete();
            $t->string('uraian');
            $t->decimal('volume', 12, 2);
            $t->string('satuan', 30);
            $t->decimal('harga_satuan', 18, 2);
            $t->decimal('jumlah', 18, 2);
            $t->index('spj_id');
        });

        Schema::create('spj_approval', function (Blueprint $t) {
            $t->id();
            $t->foreignId('spj_id')->constrained('spj')->cascadeOnDelete();
            $t->string('tahap', 30);                              // pengajuan | bendahara | ppk_skpd | pa | pembatalan
            $t->string('aksi', 30);                               // diajukan | diverifikasi | disetujui | dikembalikan | dibatalkan
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->text('catatan')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->index('spj_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spj_approval');
        Schema::dropIfExists('spj_item');
        Schema::dropIfExists('spj');
        Schema::dropIfExists('nomor_counter');
    }
};