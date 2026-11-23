<?php

namespace Tests\Support;

/**
 * Baris sheet contoh, meniru struktur laporan realisasi anggaran asli
 * (kolom A=0 ... G=6). Parser hanya membaca kolom B (1) dan C (2).
 */
class DpaFixture
{
    public const UNIT_KODE = '1.05.0.00.0.00.01.0000';

    /**
     * @param  int  $paguCetak  pagu rekening Bahan Cetak di sub kegiatan 0001
     * @param  int|null  $paguSub1  paksa pagu sub kegiatan 0001 (default = jumlah rekening)
     */
    public static function rows(int $paguCetak = 310500, ?int $paguSub1 = null): array
    {
        $unit = self::UNIT_KODE . ' - Dinas Pemadam Kebakaran dan Penyelamatan';
        $cetak = '5.1.02.01.001.00026 - Belanja Alat/Bahan untuk Kegiatan Kantor- Bahan Cetak';
        $lembur = '5.1.02.02.001.00071 - Belanja Lembur';

        return [
            ['REALISASI ANGGARAN'],
            ['BERDASARKAN PROGRAM, KEGIATAN, SUB KEGIATAN'],
            ['DINAS PEMADAM KEBAKARAN DAN PENYELAMATAN'],
            ['NO', 'KEGIATAN', 'PAGU ANGGARAN', 'REALISASI RENCANA (Rp)', 'SISA DARI REALISASI RENCANA (Rp)'],
            [null, '1.05.01 - PROGRAM PENUNJANG URUSAN PEMERINTAHAN DAERAH KABUPATEN/KOTA'],
            [null, '1.05.01.2.01 - Perencanaan, Penganggaran, dan Evaluasi Kinerja Perangkat Daerah'],
            [1, '1.05.01.2.01.0001 - Penyusunan Dokumen Perencanaan Perangkat Daerah', $paguSub1 ?? ($paguCetak + 1500000), 310400, 1500100],
            [null, $unit],
            [null, $cetak, $paguCetak, 310400, 100],
            [null, $lembur, 1500000, 0, 1500000],
            [null, null, null, null],
            [2, '1.05.01.2.01.0002 - Koordinasi dan Penyusunan Dokumen RKA-SKPD', 2025000, 450000, 1575000],
            [null, $unit],
            [null, $cetak, 525000, 450000, 75000],
            [null, $lembur, 1500000, 0, 1500000],
            [null, null, null, null],
            ['JUMLAH', null, 3835500, 760400, 3075100],
            ['PERSENTASE REALISASI KEUANGAN', null, null, '19,82%'],
        ];
    }
}