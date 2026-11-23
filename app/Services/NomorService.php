<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/** Nomor urut per OPD per tahun (atomik) dan perakit nomor dokumen, mis. 0263/NP/TAWAELI/Apr/2026. */
class NomorService
{
    private const BULAN = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    /**
     * Mengambil nomor urut berikutnya. Baris counter dikunci sehingga dua pengajuan bersamaan
     * tidak mendapat nomor yang sama. Panggil di dalam transaksi pengajuan: bila transaksi batal,
     * nomor tidak terpakai.
     */
    public function berikutnya(int $opdId, int $tahun): int
    {
        return DB::transaction(function () use ($opdId, $tahun) {
            DB::table('nomor_counter')->insertOrIgnore(['opd_id' => $opdId, 'tahun' => $tahun, 'terakhir' => 0]);

            $baris = DB::table('nomor_counter')->where('opd_id', $opdId)->where('tahun', $tahun)->lockForUpdate()->first();
            $baru = $baris->terakhir + 1;
            DB::table('nomor_counter')->where('id', $baris->id)->update(['terakhir' => $baru]);

            return $baru;
        });
    }

    /** 263, 'NP', 'TAWAELI', 18 Apr 2026 => "0263/NP/TAWAELI/Apr/2026" */
    public function format(int $urut, string $kode, string $singkatan, CarbonInterface $tanggal): string
    {
        return sprintf('%04d/%s/%s/%s/%d', $urut, $kode, $singkatan, self::BULAN[$tanggal->month], $tanggal->year);
    }
}