<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DpaController;
use App\Http\Controllers\DpaImportController;
use App\Http\Controllers\OpdController;
use App\Http\Controllers\PejabatController;
use App\Http\Controllers\PenyediaController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\PptkController;
use App\Http\Controllers\SkPenunjukanController;
use App\Http\Controllers\SpjAntrianController;
use App\Http\Controllers\SpjDokumenController;
use App\Http\Controllers\SpjPenerimaController;
use App\Http\Controllers\SpjProsesController;
use App\Http\Middleware\PeranDiizinkan;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/beranda');

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [LoginController::class, 'form'])->name('login');
    Route::post('/masuk', [LoginController::class, 'masuk'])->middleware('throttle:6,1')->name('login.proses');
});

Route::middleware('auth')->group(function () {
    Route::post('/keluar', [LoginController::class, 'keluar'])->name('logout');
    Route::get('/beranda', DashboardController::class)->name('dashboard');

    // DPA dan sisa pagu: semua peran (data dibatasi ke OPD masing-masing)
    Route::get('/dpa', [DpaController::class, 'index'])->name('dpa.index');
    Route::get('/dpa/data', [DpaController::class, 'data'])->name('dpa.data');
    Route::get('/dpa/sub/{sub}/rekening', [DpaController::class, 'rekening'])->name('dpa.rekening');

    // Impor DPA dari Excel: superadmin, admin OPD, bendahara
    Route::middleware(PeranDiizinkan::class . ':superadmin,admin,bendahara')->prefix('dpa/impor')->name('dpa.')->group(function () {
        Route::get('/', [DpaImportController::class, 'form'])->name('impor');
        Route::post('/', [DpaImportController::class, 'stage'])->name('impor.stage');
        Route::get('/{batch}', [DpaImportController::class, 'show'])->name('impor.show');
        Route::get('/{batch}/baris', [DpaImportController::class, 'baris'])->name('impor.baris');
        Route::post('/{batch}/komit', [DpaImportController::class, 'komit'])->name('impor.komit');
        Route::delete('/{batch}', [DpaImportController::class, 'buang'])->name('impor.buang');
    });

    // SPJ
    Route::middleware(PeranDiizinkan::class . ':superadmin,admin,bendahara,pptk,ppk_skpd,pa')->prefix('spj')->name('spj.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SpjController::class, 'index'])->name('index');
        Route::get('/data', [\App\Http\Controllers\SpjController::class, 'data'])->name('data');
        
        // --- PINDAHKAN ROUTE ANTRIAN KE SINI (SEBELUM /{spj}) ---
        Route::middleware(PeranDiizinkan::class . ':bendahara,ppk_skpd,pa')->group(function () {
            Route::get('/antrian', [SpjAntrianController::class, 'index'])->name('antrian');
            Route::get('/antrian/data', [SpjAntrianController::class, 'data'])->name('antrian.data');
        });

        Route::get('spj/{spj}/dokumen/semua', [SpjDokumenController::class, 'semua'])->name('dokumen.semua');
        Route::post('spj/{spj}/dokumen/pemeriksaan', [SpjDokumenController::class, 'pemeriksaan'])
            ->middleware(PeranDiizinkan::class . ':bendahara,pptk,ppk_skpd')->name('dokumen.pemeriksaan');
        Route::get('spj/{spj}/dokumen/{jenis}', [SpjDokumenController::class, 'satu'])->name('dokumen');
        // ---------------------------------------------------------

        // Hanya bendahara dan pptk yang bisa buat/edit
        // Route::middleware(PeranDiizinkan::class . ':superadmin,bendahara,pptk')->group(function () {
        //     Route::get('spj/penerima/templat', [SpjPenerimaController::class, 'templat'])->name('spj.penerima.templat');
        //     Route::post('spj/penerima/impor', [SpjPenerimaController::class, 'impor'])->name('spj.penerima.impor');
        //     Route::get('/baru', [\App\Http\Controllers\SpjController::class, 'create'])->name('create');
        //     Route::post('/', [\App\Http\Controllers\SpjController::class, 'store'])->name('store');
        //     Route::get('/{spj}/ubah', [\App\Http\Controllers\SpjController::class, 'edit'])->name('edit');
        //     Route::put('/{spj}', [\App\Http\Controllers\SpjController::class, 'update'])->name('update');
        //     Route::delete('/{spj}', [\App\Http\Controllers\SpjController::class, 'destroy'])->name('destroy');
        // });

        Route::middleware(PeranDiizinkan::class . ':superadmin,bendahara,pptk')->group(function () {
            Route::get('/penerima/templat', [\App\Http\Controllers\SpjPenerimaController::class, 'templat'])->name('penerima.templat');
            Route::post('/penerima/impor', [\App\Http\Controllers\SpjPenerimaController::class, 'impor'])->name('penerima.impor');
            Route::get('/baru', [\App\Http\Controllers\SpjController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\SpjController::class, 'store'])->name('store');
            Route::get('/{spj}/ubah', [\App\Http\Controllers\SpjController::class, 'edit'])->name('edit');
            Route::put('/{spj}', [\App\Http\Controllers\SpjController::class, 'update'])->name('update');
            Route::delete('/{spj}', [\App\Http\Controllers\SpjController::class, 'destroy'])->name('destroy');
        });

        // Semua role bisa lihat detail, tapi yang bisa ajukan hanya yang berwenang (dicek di controller)
        Route::get('/{spj}', [\App\Http\Controllers\SpjController::class, 'show'])->name('show');
        Route::post('/{spj}/ajukan', [\App\Http\Controllers\SpjController::class, 'ajukan'])->name('ajukan');

        Route::get('/{spj}/cetak', [\App\Http\Controllers\SpjController::class, 'cetak'])->name('cetak');
    });

    

    // Hapus route antrian yang lama di bawah sini
    // Route::middleware(PeranDiizinkan::class . ':bendahara,ppk_skpd,pa')->group(function () {
    //     Route::get('spj/antrian', [SpjAntrianController::class, 'index'])->name('spj.antrian');
    //     Route::get('spj/antrian/data', [SpjAntrianController::class, 'data'])->name('spj.antrian.data');
    // });
    
    // Route proses tetap biarkan di luar atau di dalam, pastikan tidak bentrok
    Route::middleware(PeranDiizinkan::class . ':bendahara,ppk_skpd,pa')->group(function () {
        Route::post('spj/{spj}/proses', SpjProsesController::class)->name('spj.proses');
    });

    // Data master OPD: hanya superadmin
    Route::middleware(PeranDiizinkan::class . ':superadmin')->prefix('opd')->name('opd.')->group(function () {
        Route::get('/', [OpdController::class, 'index'])->name('index');
        Route::get('/data', [OpdController::class, 'data'])->name('data');
        Route::get('/baru', [OpdController::class, 'create'])->name('create');
        Route::post('/', [OpdController::class, 'store'])->name('store');
        Route::get('/{opd}/ubah', [OpdController::class, 'edit'])->name('edit');
        Route::put('/{opd}', [OpdController::class, 'update'])->name('update');
        
        // TAMBAHKAN BARIS INI UNTUK HAPUS
        Route::delete('/{opd}', [OpdController::class, 'destroy'])->name('destroy');
    });

    // Pengguna: superadmin dan admin OPD (pembatasan OPD di controller)
    Route::middleware(PeranDiizinkan::class . ':superadmin,admin')->prefix('pengguna')->name('pengguna.')->group(function () {
        Route::get('/', [PenggunaController::class, 'index'])->name('index');
        Route::get('/data', [PenggunaController::class, 'data'])->name('data');
        Route::get('/baru', [PenggunaController::class, 'create'])->name('create');
        Route::post('/', [PenggunaController::class, 'store'])->name('store');

        // Pastikan dua route ini ada dan namanya sesuai
        Route::post('/{user}/reset-password', [PenggunaController::class, 'resetPassword'])->name('reset-password');
        Route::delete('/{user}', [PenggunaController::class, 'destroy'])->name('destroy');

        Route::get('/{user}/ubah', [PenggunaController::class, 'edit'])->name('edit');
        Route::put('/{user}', [PenggunaController::class, 'update'])->name('update');
    });
    
    // Pejabat penandatangan dan SK penunjukan: superadmin, admin OPD
    Route::middleware(PeranDiizinkan::class . ':superadmin,admin')->group(function () {
        Route::get('pejabat/data', [PejabatController::class, 'data'])->name('pejabat.data');
        // Hapus 'destroy' dari except
        Route::resource('pejabat', PejabatController::class)->except(['show'])->parameters(['pejabat' => 'pejabat']);
        
        Route::get('sk/data', [SkPenunjukanController::class, 'data'])->name('sk.data');
        // Hapus 'destroy' dari except
        Route::resource('sk', SkPenunjukanController::class)->except(['show'])->parameters(['sk' => 'sk']);
    });

    // Penyedia dan penugasan PPTK: superadmin, admin OPD, bendahara
    Route::middleware(PeranDiizinkan::class . ':superadmin,admin,bendahara')->group(function () {
        Route::get('penyedia/data', [PenyediaController::class, 'data'])->name('penyedia.data');
        // Route::resource('penyedia', PenyediaController::class)->except(['show', 'destroy'])->parameters(['penyedia' => 'penyedia']);
        Route::resource('penyedia', PenyediaController::class)->except(['show'])->parameters(['penyedia' => 'penyedia']);

        Route::get('pptk', [PptkController::class, 'index'])->name('pptk.index');
        Route::get('pptk/data', [PptkController::class, 'data'])->name('pptk.data');
        Route::get('pptk/{user}/tugas', [PptkController::class, 'edit'])->name('pptk.edit');
        Route::put('pptk/{user}/tugas', [PptkController::class, 'update'])->name('pptk.update');
    });

    // Ganti Password
    Route::get('/ganti-password', [\App\Http\Controllers\ProfilController::class, 'formPassword'])->name('profil.password');
    Route::put('/ganti-password', [\App\Http\Controllers\ProfilController::class, 'gantiPassword'])->name('profil.password.update');

    // Verifikasi & Persetujuan
    Route::middleware(PeranDiizinkan::class . ':bendahara,ppk_skpd,pa')->prefix('verifikasi')->name('verifikasi.')->group(function () {
        Route::get('/', [\App\Http\Controllers\VerifikasiController::class, 'index'])->name('index');
        Route::post('/{spj}/proses', [\App\Http\Controllers\VerifikasiController::class, 'proses'])->name('proses');
    });

});