<?php

/**
 * Pengaturan dokumen cetak SPJ. Nilainya bisa diubah lewat .env tanpa mengubah kode.
 */
return [
    // Baris pertama kop dokumen.
    'pemda' => env('SPJ_PEMDA', 'PEMERINTAH KOTA PALU'),

    // Nama kota pada baris tanggal tanda tangan, mis. "Palu, 18 April 2026".
    'kota' => env('SPJ_KOTA', 'Palu'),

    // Dasar hukum pengadaan yang dikutip di Berita Acara Pengadaan. Periksa kembali agar sesuai
    // peraturan yang berlaku di daerah Anda.
    'dasar_hukum_pengadaan' => env(
        'SPJ_DASAR_HUKUM_PENGADAAN',
        'Peraturan Presiden Republik Indonesia Nomor 16 Tahun 2018 tentang Pengadaan Barang/Jasa Pemerintah'
    ),
];