<?php

namespace App\Exceptions;

use App\Support\Uang;

class PaguTidakCukupException extends SpjException
{
    public function __construct(public readonly int $sisaSen, public readonly int $dibutuhkanSen)
    {
        parent::__construct(sprintf(
            'Pagu rekening tidak cukup. Sisa pagu %s, SPJ ini membutuhkan %s.',
            Uang::rupiah($sisaSen), Uang::rupiah($dibutuhkanSen)
        ));
    }
}