<?php

namespace App\Support;

/** Angka ke kata dalam bahasa Indonesia. */
class Terbilang
{
    private const SATUAN = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

    /** 1578309 => "satu juta lima ratus tujuh puluh delapan ribu tiga ratus sembilan" */
    public static function angka(int $n): string
    {
        if ($n === 0) {
            return 'nol';
        }

        return ($n < 0 ? 'minus ' : '') . self::baca(abs($n));
    }

    /** Dengan huruf awal kapital tiap kata: "Satu Juta Lima Ratus ..." */
    public static function judul(int $n): string
    {
        return ucwords(self::angka($n));
    }

    /** Sen => "Satu Juta Lima Ratus Tujuh Puluh Delapan Ribu Tiga Ratus Sembilan Rupiah" (sen ditambahkan bila ada). */
    public static function rupiah(int $sen): string
    {
        $rupiah = intdiv(abs($sen), 100);
        $sisa = abs($sen) % 100;
        $teks = ($sen < 0 ? 'Minus ' : '') . ucwords(self::baca($rupiah) ?: 'nol') . ' Rupiah';

        return $sisa > 0 ? $teks . ' ' . ucwords(self::baca($sisa)) . ' Sen' : $teks;
    }

    private static function baca(int $n): string
    {
        return match (true) {
            $n < 12 => self::SATUAN[$n],
            $n < 20 => self::baca($n - 10) . ' belas',
            $n < 100 => self::baca(intdiv($n, 10)) . ' puluh' . self::sisa($n % 10),
            $n < 200 => 'seratus' . self::sisa($n - 100),
            $n < 1000 => self::baca(intdiv($n, 100)) . ' ratus' . self::sisa($n % 100),
            $n < 2000 => 'seribu' . self::sisa($n - 1000),
            $n < 1000000 => self::baca(intdiv($n, 1000)) . ' ribu' . self::sisa($n % 1000),
            $n < 1000000000 => self::baca(intdiv($n, 1000000)) . ' juta' . self::sisa($n % 1000000),
            $n < 1000000000000 => self::baca(intdiv($n, 1000000000)) . ' miliar' . self::sisa($n % 1000000000),
            default => self::baca(intdiv($n, 1000000000000)) . ' triliun' . self::sisa($n % 1000000000000),
        };
    }

    private static function sisa(int $n): string
    {
        return $n > 0 ? ' ' . self::baca($n) : '';
    }
}