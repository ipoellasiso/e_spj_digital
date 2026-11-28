<?php

namespace App\Services;

use App\Exceptions\PaguTidakCukupException;
use App\Models\AnggaranRekening;
use App\Models\Spj;
use App\Support\Uang;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Satu-satunya tempat yang boleh mengubah anggaran_rekening.reserved dan realisasi_final.
 * Setiap perubahan mengunci baris rekening (lockForUpdate) di dalam transaksi, sehingga dua SPJ
 * yang diajukan bersamaan ke rekening yang sama tidak bisa sama-sama lolos melebihi pagu.
 *
 * sisa = pagu - realisasi_final - reserved
 */
class PaguService
{
    /** Sisa pagu (sen) tanpa mengunci; untuk tampilan. */
    // public function sisaSen(AnggaranRekening $ar): int
    // {
    //     return Uang::sen($ar->pagu) - Uang::sen($ar->realisasi_final) - Uang::sen($ar->reserved);
    // }
    public function sisaSen(AnggaranRekening $ar, ?int $kecualiSpjId = null) {
        $reserved = Uang::sen($ar->reserved);
        if ($kecualiSpjId) {
            // Kurangi reserved dengan total SPJ yang sedang diverifikasi
            $spj = Spj::find($kecualiSpjId);
            if ($spj) {
                $reserved -= Uang::sen($spj->total);
            }
        }
        return Uang::sen($ar->pagu) - Uang::sen($ar->realisasi_final) - $reserved;
    }

    /** Tahan pagu sebesar total SPJ (saat diajukan). Melempar PaguTidakCukupException bila sisa tidak cukup. */
    public function reserve(Spj $spj): void
    {
        $this->ubah($spj, function (int $pagu, int $final, int $reserved, int $total) {
            $sisa = $pagu - $final - $reserved;
            if ($total > $sisa) {
                throw new PaguTidakCukupException($sisa, $total);
            }

            return [$final, $reserved + $total];
        });
    }

    /** Lepas pagu yang ditahan (SPJ ditolak, dikembalikan, atau dibatalkan sebelum final). */
    public function release(Spj $spj): void
    {
        $this->ubah($spj, function (int $pagu, int $final, int $reserved, int $total) {
            $this->pastikan($reserved >= $total, 'Pagu yang ditahan lebih kecil dari total SPJ.');

            return [$final, $reserved - $total];
        });
    }

    /** SPJ final: pindahkan dari reserved ke realisasi_final. */
    public function realisasi(Spj $spj): void
    {
        $this->ubah($spj, function (int $pagu, int $final, int $reserved, int $total) {
            $this->pastikan($reserved >= $total, 'Pagu yang ditahan lebih kecil dari total SPJ.');

            return [$final + $total, $reserved - $total];
        });
    }

    /** SPJ final dibatalkan: kurangi realisasi_final. */
    public function batalkanRealisasi(Spj $spj): void
    {
        $this->ubah($spj, function (int $pagu, int $final, int $reserved, int $total) {
            $this->pastikan($final >= $total, 'Realisasi lebih kecil dari total SPJ.');

            return [$final - $total, $reserved];
        });
    }

    /** @param callable(int,int,int,int): array{0:int,1:int} $hitung menerima pagu, final, reserved, total (sen); mengembalikan [final, reserved] baru */
    private function ubah(Spj $spj, callable $hitung): void
    {
        $alokasi = $this->alokasi($spj);
        ksort($alokasi);

        DB::transaction(function () use ($alokasi, $hitung) {
            foreach ($alokasi as $arId => $total) {
                $ar = AnggaranRekening::withoutGlobalScopes()->lockForUpdate()->findOrFail($arId);
                [$final, $reserved] = $hitung(
                    Uang::sen($ar->pagu), Uang::sen($ar->realisasi_final), Uang::sen($ar->reserved), $total
                );
                $ar->update(['realisasi_final' => Uang::format($final), 'reserved' => Uang::format($reserved)]);
            }
        });
    }

    private function pastikan(bool $kondisi, string $pesan): void
    {
        if (! $kondisi) {
            throw new LogicException('Data pagu tidak konsisten: ' . $pesan . ' Jalankan rekonsiliasi pagu.');
        }
    }

    /** Alokasi pagu per rekening (sen): [anggaran_rekening_id => total]. Item tanpa rekening ikut rekening SPJ. */
    public function alokasi(Spj $spj): array
    {
        $hasil = [];
        foreach ($spj->items()->get(['anggaran_rekening_id', 'jumlah']) as $it) {
            $id = $it->anggaran_rekening_id ?? $spj->anggaran_rekening_id;
            $hasil[$id] = ($hasil[$id] ?? 0) + Uang::sen($it->jumlah);
        }

        return $hasil ?: [$spj->anggaran_rekening_id => Uang::sen($spj->total)];
    }

    /** Posisi pagu tiap rekening yang dipakai SPJ, untuk halaman show. */
    public function posisi(Spj $spj): array
    {
        $alokasi = $this->alokasi($spj);

        return AnggaranRekening::withoutGlobalScopes()->with('rekening')->whereIn('id', array_keys($alokasi))->get()
            ->sortBy(fn ($r) => $r->rekening->kode)
            ->map(fn ($ar) => [
                'kode' => $ar->rekening->kode, 'nama' => $ar->rekening->nama,
                'pagu' => Uang::sen($ar->pagu), 'sisa' => $this->sisaSen($ar), 'ini' => $alokasi[$ar->id],
            ])->values()->all();
    }
}