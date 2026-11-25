<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

/** Pembaca mentah: dipakai lewat Excel::toArray(new LembarPertama, $berkas)[0]. */
class LembarPertama implements ToArray
{
    public function array(array $array): void
    {
        // sengaja kosong; hasil dibaca lewat Excel::toArray()
    }
}