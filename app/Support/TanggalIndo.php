<?php

namespace App\Support;

use Carbon\CarbonInterface;

/** Format tanggal bahasa Indonesia untuk dokumen resmi. */
class TanggalIndo
{
    private const HARI = [0 => 'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    private const BULAN = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    public static function hari(CarbonInterface $t): string
    {
        return self::HARI[$t->dayOfWeek];
    }

    public static function bulan(CarbonInterface $t): string
    {
        return self::BULAN[$t->month];
    }

    /** "18 April 2026" */
    public static function panjang(CarbonInterface $t): string
    {
        return $t->day . ' ' . self::bulan($t) . ' ' . $t->year;
    }

    /** "Sabtu tanggal Delapan Belas bulan April tahun Dua Ribu Dua Puluh Enam" */
    public static function kata(CarbonInterface $t): string
    {
        return sprintf(
            '%s tanggal %s bulan %s tahun %s',
            self::hari($t), Terbilang::judul($t->day), self::bulan($t), Terbilang::judul($t->year)
        );
    }
}