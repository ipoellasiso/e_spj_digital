<?php

namespace App\Enums;

enum SpjStatus: string
{
    case Draft = 'draft';
    case Diajukan = 'diajukan';
    case DiverifikasiBendahara = 'diverifikasi_bendahara';
    case DiverifikasiPpk = 'diverifikasi_ppk';
    case Final = 'final';
    case Dikembalikan = 'dikembalikan';
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Diajukan => 'Diajukan',
            self::DiverifikasiBendahara => 'Diverifikasi bendahara',
            self::DiverifikasiPpk => 'Diverifikasi PPK-SKPD',
            self::Final => 'Final',
            self::Dikembalikan => 'Dikembalikan',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    /** Kelas CSS badge status (lihat public/css/tema-spj.css). */
    public function badge(): string
    {
        return match ($this) {
            self::Draft => 'st-draft',
            self::Diajukan => 'st-diajukan',
            self::DiverifikasiBendahara, self::DiverifikasiPpk => 'st-verif',
            self::Final => 'st-final',
            self::Dikembalikan => 'st-kembali',
            self::Dibatalkan => 'st-batal',
        };
    }

    /** Hanya draft dan yang dikembalikan yang boleh diedit atau diajukan. */
    public function bisaDiubah(): bool
    {
        return $this === self::Draft || $this === self::Dikembalikan;
    }

    /** Status yang menahan (reserve) pagu rekening. */
    public function menahanPagu(): bool
    {
        return in_array($this, [self::Diajukan, self::DiverifikasiBendahara, self::DiverifikasiPpk], true);
    }
}