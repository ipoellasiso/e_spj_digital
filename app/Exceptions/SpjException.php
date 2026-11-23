<?php

namespace App\Exceptions;

use RuntimeException;

/** Pelanggaran aturan SPJ. $daftar memuat satu atau beberapa alasan untuk ditampilkan ke pengguna. */
class SpjException extends RuntimeException
{
    /** @var string[] */
    public array $daftar;

    public function __construct(string|array $pesan)
    {
        $this->daftar = (array) $pesan;
        parent::__construct(implode("\n", $this->daftar));
    }
}