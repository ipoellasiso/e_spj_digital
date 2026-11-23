<?php

namespace App\Enums;

enum SpjStatus: string
{
    case Draft = 'draft';
    case Diajukan = 'diajukan';
    
    // PASTIKAN BARIS INI SAMA PERSIS DENGAN DATABASE
    case DiverifikasiBendahara = 'diverifikasi_bendahara'; 
    
    case DiverifikasiPpk = 'diverifikasi_ppk';
    case Final = 'final';
    case Dikembalikan = 'dikembalikan';
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match($this) {
            self::Draft => 'Draft',
            self::Diajukan => 'Menunggu Bendahara',
            self::DiverifikasiBendahara => 'Menunggu PPK',
            self::DiverifikasiPpk => 'Menunggu PA',
            self::Final => 'Final (Disetujui)',
            self::Dikembalikan => 'Dikembalikan',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::Draft => 'bg-secondary',
            self::Diajukan, self::DiverifikasiBendahara, self::DiverifikasiPpk => 'bg-warning text-dark',
            self::Final => 'bg-success',
            self::Dikembalikan => 'bg-danger',
            self::Dibatalkan => 'bg-dark',
        };
    }

    public function bisaDiubah(): bool
    {
        return in_array($this, [self::Draft, self::Dikembalikan]);
    }
}