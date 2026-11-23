<?php

namespace App\Support;

use App\Services\DpaParser;

/** Semua hitungan uang memakai integer sen supaya bebas galat pembulatan float. */
class Uang
{
    /** Dari nilai database (decimal, int, float) ke sen. */
    public static function sen(mixed $nilai): int
    {
        return (int) round(((float) $nilai) * 100);
    }

    /** Sen ke string desimal tepat, mis. 181050000 => "1810500.00". */
    public static function format(int $sen): string
    {
        return DpaParser::formatSen($sen);
    }

    /** Sen ke tampilan rupiah, mis. 157830900 => "Rp1.578.309,00". */
    public static function rupiah(int $sen): string
    {
        return ($sen < 0 ? '-' : '') . 'Rp' . number_format(abs($sen) / 100, 2, ',', '.');
    }

    /** Input harga dari pengguna ("1.578.309", "1578309", "1.578.309,50") ke sen; null bila bukan angka. */
    public static function dariInput(mixed $nilai): ?int
    {
        return DpaParser::keSen(is_string($nilai) ? trim($nilai) : $nilai);
    }

    /** Input volume ("1", "2,5", "0.75") ke persepuluh-ratus (1 => 100). Maksimal 2 desimal. Null bila tidak valid. */
    public static function volumeSeratus(mixed $nilai): ?int
    {
        $s = str_replace(',', '.', trim((string) $nilai));
        if (! preg_match('/^\d+(\.\d{1,2})?$/', $s)) {
            return null;
        }

        return (int) round(((float) $s) * 100);
    }

    /** Jumlah = harga satuan x volume, dibulatkan ke sen. */
    public static function jumlahSen(int $hargaSen, int $volumeSeratus): int
    {
        return (int) round($hargaSen * $volumeSeratus / 100);
    }

    /** Sen ke teks isian harga, mis. 157830900 => "1.578.309", 157830950 => "1.578.309,50". */
    public static function isianHarga(mixed $nilai): string
    {
        $sen = self::sen($nilai);

        return number_format($sen / 100, $sen % 100 === 0 ? 0 : 2, ',', '.');
    }

    /** Volume desimal database ("1.50") ke teks isian ("1,5"). */
    public static function isianVolume(mixed $nilai): string
    {
        $s = rtrim(rtrim(number_format((float) $nilai, 2, '.', ''), '0'), '.');

        return str_replace('.', ',', $s);
    }
}