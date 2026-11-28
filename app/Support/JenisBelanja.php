<?php

namespace App\Support;

/** Mapping kode rekening ke jenis belanja untuk rekap SPJ. */
class JenisBelanja
{
    /**
     * Daftar Exact Match Kode Rekening.
     * Kita petakan persis ke kategori laporan yang rapi.
     */
    public const MAP_EXACT = [
        // ==========================================
        // 1. PERJALANAN DINAS
        // ==========================================
        '5.1.02.04.001.00001' => 'Perjalanan Dinas',
        '5.1.02.04.001.00003' => 'Perjalanan Dinas',

        // ==========================================
        // 2. MAKAN DAN MINUM
        // ==========================================
        '5.1.02.01.001.00058' => 'Makan dan Minum',
        '5.1.02.01.001.00052' => 'Makan dan Minum',
        '5.1.02.01.001.00053' => 'Makan dan Minum',
        '5.1.02.01.001.00056' => 'Makan dan Minum',
        '5.1.02.01.001.00043' => 'Makan dan Minum', // Natura

        // ==========================================
        // 3. ALAT TULIS KANTOR (ATK) & CETAK
        // ==========================================
        '5.1.02.01.001.00026' => 'Belanja ATK',
        '5.1.02.01.001.00027' => 'Belanja ATK',
        '5.1.02.01.001.00025' => 'Belanja ATK',
        '5.1.02.01.001.00029' => 'Belanja ATK',
        '5.1.02.01.001.00031' => 'Belanja ATK',
        '5.1.02.01.001.00036' => 'Belanja ATK',
        '5.1.02.01.001.00024' => 'Belanja ATK',
        '5.1.02.01.001.00034' => 'Belanja ATK',

        // ==========================================
        // 4. HONORARIUM & JASA PEGAWAI
        // ==========================================
        '5.1.02.02.001.00071' => 'Honorarium & Jasa',
        '5.1.02.02.001.00006' => 'Honorarium & Jasa',
        '5.1.02.02.001.00026' => 'Honorarium & Jasa',
        '5.1.02.02.001.00027' => 'Honorarium & Jasa',
        '5.1.02.02.001.00030' => 'Honorarium & Jasa',
        '5.1.02.02.001.00031' => 'Honorarium & Jasa',
        '5.1.02.02.001.00080' => 'Honorarium & Jasa',
        '5.1.02.02.001.00081' => 'Honorarium & Jasa',
        '5.1.02.02.001.00088' => 'Honorarium & Jasa',
        '5.1.02.02.001.00089' => 'Honorarium & Jasa',
        '5.1.02.02.001.00003' => 'Honorarium & Jasa',
        '5.1.02.02.001.00004' => 'Honorarium & Jasa',
        '5.1.02.02.001.00016' => 'Honorarium & Jasa',
        '5.1.02.02.001.00028' => 'Honorarium & Jasa',
        '5.1.02.02.001.00038' => 'Honorarium & Jasa',
        '5.1.02.02.001.00014' => 'Honorarium & Jasa',

        // ==========================================
        // 5. PEMELIHARAAN
        // ==========================================
        '5.1.02.03.002.00035' => 'Belanja Pemeliharaan',
        '5.1.02.03.002.00038' => 'Belanja Pemeliharaan',
        '5.1.02.03.002.00039' => 'Belanja Pemeliharaan',
        '5.1.02.03.002.00405' => 'Belanja Pemeliharaan',
        '5.1.02.03.002.00409' => 'Belanja Pemeliharaan',
        '5.1.02.03.002.00121' => 'Belanja Pemeliharaan',
        '5.1.02.03.003.00032' => 'Belanja Pemeliharaan',
        '5.1.02.03.003.00063' => 'Belanja Pemeliharaan',
        '5.1.02.03.002.00036' => 'Belanja Pemeliharaan',
        '5.1.02.03.003.00001' => 'Belanja Pemeliharaan',
        '5.1.02.03.004.00004' => 'Belanja Pemeliharaan',
        '5.1.02.03.004.00054' => 'Belanja Pemeliharaan',

        // ==========================================
        // 6. JASA & SEWA (Listrik, Internet, Sewa Gedung, dll)
        // ==========================================
        '5.1.02.02.001.00051' => 'Jasa & Sewa',
        '5.1.02.02.001.00055' => 'Jasa & Sewa',
        '5.1.02.02.001.00062' => 'Jasa & Sewa',
        '5.1.02.02.001.00061' => 'Jasa & Sewa',
        '5.1.02.02.001.00063' => 'Jasa & Sewa',
        '5.1.02.02.001.00047' => 'Jasa & Sewa',
        '5.1.02.02.001.00065' => 'Jasa & Sewa',
        '5.1.02.02.001.00060' => 'Jasa & Sewa',
        '5.1.02.02.004.00036' => 'Jasa & Sewa',
        '5.1.02.02.004.00118' => 'Jasa & Sewa',
        '5.1.02.02.005.00032' => 'Jasa & Sewa',
        '5.1.02.02.005.00009' => 'Jasa & Sewa',
        '5.1.02.02.005.00043' => 'Jasa & Sewa',
        '5.1.02.02.007.00030' => 'Jasa & Sewa',
        '5.1.02.02.007.00035' => 'Jasa & Sewa',
        '5.1.02.02.005.00001' => 'Jasa & Sewa',

        // ==========================================
        // 7. PAKAIAN & SERAGAM
        // ==========================================
        '5.1.02.01.001.00063' => 'Pakaian & Seragam',
        '5.1.02.01.001.00075' => 'Pakaian & Seragam',
        '5.1.02.01.001.00040' => 'Pakaian & Seragam', // Barang diserahkan ke pihak ketiga

        // ==========================================
        // 8. BAHAN BAKAR, SUKU CADANG & BAHAN BAKU
        // ==========================================
        '5.1.02.01.001.00004' => 'Bahan Bakar & Suku Cadang',
        '5.1.02.01.001.00023' => 'Bahan Bakar & Suku Cadang',
        '5.1.02.01.001.00013' => 'Bahan Bakar & Suku Cadang',
        '5.1.02.01.001.00020' => 'Bahan Bakar & Suku Cadang',
        '5.1.02.01.001.00012' => 'Bahan Bakar & Suku Cadang',
        '5.1.02.01.001.00005' => 'Bahan Bakar & Suku Cadang',

        // ==========================================
        // 9. IURAN & PAJAK
        // ==========================================
        '5.1.02.02.002.00006' => 'Iuran & Pajak',
        '5.1.02.02.002.00007' => 'Iuran & Pajak',
        '5.1.02.02.002.00019' => 'Iuran & Pajak',
        '5.1.02.02.002.00020' => 'Iuran & Pajak',
        '5.1.02.02.002.00027' => 'Iuran & Pajak',
        '5.1.02.02.002.00028' => 'Iuran & Pajak',
        '5.1.02.02.002.00035' => 'Iuran & Pajak',
        '5.1.02.02.002.00036' => 'Iuran & Pajak',
        '5.1.02.02.001.00067' => 'Iuran & Pajak',

        // ==========================================
        // 10. HADIAH & PENGHARGAAN
        // ==========================================
        '5.1.02.05.001.00001' => 'Hadiah & Penghargaan',
        '5.1.02.05.001.00002' => 'Hadiah & Penghargaan',
        '5.1.02.01.001.00039' => 'Hadiah & Penghargaan',
    ];

    /** 
     * Fallback cerdas (jika kode rekening tidak ada di MAP_EXACT).
     * Berdasarkan kata kunci pada nama rekening.
     */
    public static function dariKode(?string $kode, ?string $nama = null): string
    {
        if (!$kode) return 'Lain-lain';

        // 1. Cek Exact Match (Paling Akurat)
        if (isset(self::MAP_EXACT[$kode])) {
            return self::MAP_EXACT[$kode];
        }

        // 2. Cek berdasarkan awalan (jika kode baru tidak terdaftar)
        if (str_starts_with($kode, '5.1.02.04')) return 'Perjalanan Dinas';
        
        // 3. Cek kata kunci dari nama rekening (jika $nama dikirim)
        if ($nama) {
            $n = strtolower($nama);
            if (str_contains($n, 'makan') || str_contains($n, 'minum') || str_contains($n, 'natura')) return 'Makan dan Minum';
            if (str_contains($n, 'pemeliharaan')) return 'Belanja Pemeliharaan';
            if (str_contains($n, 'honor')) return 'Honorarium & Jasa';
            if (str_contains($n, 'sewa')) return 'Jasa & Sewa';
            if (str_contains($n, 'atk') || str_contains($n, 'kertas') || str_contains($n, 'cetak')) return 'Belanja ATK';
        }

        return 'Lain-lain';
    }
}