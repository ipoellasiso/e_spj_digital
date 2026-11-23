<?php

namespace App\Support;

/** NIP disimpan 18 digit tanpa spasi; ditampilkan 8-6-1-3 seperti di dokumen resmi. */
class Nip
{
    public static function bersihkan(?string $nip): ?string
    {
        $angka = preg_replace('/\D/', '', (string) $nip);

        return $angka === '' ? null : $angka;
    }

    public static function format(?string $nip): string
    {
        if ($nip === null || strlen($nip) !== 18) {
            return (string) $nip;
        }

        return substr($nip, 0, 8) . ' ' . substr($nip, 8, 6) . ' ' . substr($nip, 14, 1) . ' ' . substr($nip, 15, 3);
    }
}