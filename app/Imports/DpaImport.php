<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

/**
 * Pembaca mentah: dipakai lewat Excel::toArray(new DpaImport, $path).
 * Seluruh logika parsing ada di App\Services\DpaParser.
 */
class DpaImport implements ToArray
{
    public function array(array $array): void
    {
        // sengaja kosong; hasil dibaca lewat Excel::toArray()
    }
}