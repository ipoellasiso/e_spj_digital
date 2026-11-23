<?php

namespace App\Services;

use App\Enums\SpjStatus;
use App\Exceptions\SpjException;
use App\Models\Spj;
use App\Models\SpjApproval;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Alur persetujuan SPJ berjenjang:
 *
 *   diajukan --(bendahara)--> diverifikasi_bendahara --(PPK-SKPD)--> diverifikasi_ppk --(PA)--> final
 *
 * - Penolakan di tahap mana pun (wajib catatan) mengembalikan SPJ ke pembuatnya dan melepas pagu yang ditahan.
 * - SPJ final hanya dibatalkan oleh PA (wajib catatan); realisasinya dikembalikan.
 * - Setiap perubahan mengunci baris SPJ, sehingga klik ganda atau dua orang yang bertindak bersamaan
 *   tidak bisa memproses tahap yang sama dua kali.
 *
 * Pemisahan tugas (dihitung pada siklus pengajuan yang sedang berjalan):
 * - satu orang tidak boleh memproses dua tahap berbeda pada SPJ yang sama;
 * - pengaju SPJ tidak boleh memverifikasi di tahap PPK-SKPD atau menyetujui di tahap PA.
 */
class SpjWorkflowService
{
    /** status saat ini => peran yang bertindak, nama tahap di riwayat, status berikutnya, label tombol */
    private const TAHAP = [
        'diajukan' => ['peran' => 'bendahara', 'tahap' => 'bendahara', 'maju' => SpjStatus::DiverifikasiBendahara, 'label' => 'Verifikasi'],
        'diverifikasi_bendahara' => ['peran' => 'ppk_skpd', 'tahap' => 'ppk_skpd', 'maju' => SpjStatus::DiverifikasiPpk, 'label' => 'Verifikasi'],
        'diverifikasi_ppk' => ['peran' => 'pa', 'tahap' => 'pa', 'maju' => SpjStatus::Final, 'label' => 'Setujui'],
    ];

    public function __construct(private PaguService $pagu)
    {
    }

    // ------------------------------------------------------------ untuk tampilan

    /**
     * Tindakan yang tersedia bagi $user pada $spj.
     *
     * @return array{maju: ?string, kembalikan: bool, batalkan: bool, tertahan: ?string}
     */
    public function tindakanTersedia(Spj $spj, User $user): array
    {
        $hasil = ['maju' => null, 'kembalikan' => false, 'batalkan' => false, 'tertahan' => null];

        $t = self::TAHAP[$spj->status->value] ?? null;
        if ($t && $this->sesuai($spj, $user, $t['peran'])) {
            $hasil['kembalikan'] = true;
            try {
                $this->periksaPemisahanTugas($spj, $user, $t['tahap']);
                $hasil['maju'] = $t['label'];
            } catch (SpjException $e) {
                $hasil['tertahan'] = $e->getMessage();
            }
        }

        if ($spj->status === SpjStatus::Final && $this->sesuai($spj, $user, 'pa')) {
            $hasil['batalkan'] = true;
        }
        if ($spj->status === SpjStatus::Dikembalikan && ($this->sesuai($spj, $user, 'bendahara') || $this->sesuai($spj, $user, 'pa'))) {
            $hasil['batalkan'] = true;
        }

        return $hasil;
    }

    /** Nilai status SPJ yang sedang menunggu tindakan $user, menurut perannya. */
    public function statusMenunggu(User $user): array
    {
        $peta = ['bendahara' => 'diajukan', 'ppk_skpd' => 'diverifikasi_bendahara', 'pa' => 'diverifikasi_ppk'];

        return collect($peta)->filter(fn ($status, $peran) => $user->hasRole($peran))->values()->all();
    }

    public function jumlahMenunggu(User $user): int
    {
        $status = $this->statusMenunggu($user);
        if (! $status || ! $user->opd_id) {
            return 0;
        }

        return Spj::withoutGlobalScopes()->where('opd_id', $user->opd_id)->whereIn('status', $status)->count();
    }

    // ------------------------------------------------------------------ tindakan

    /** Verifikasi atau persetujuan sesuai tahap SPJ saat ini. */
    // public function maju(Spj $spj, User $user, ?string $catatan = null): Spj
    // {
    //     return DB::transaction(function () use ($spj, $user, $catatan) {
    //         $s = $this->kunci($spj);
    //         $t = self::TAHAP[$s->status->value] ?? throw new SpjException('SPJ ini tidak sedang menunggu verifikasi atau persetujuan.');

    //         $this->periksaPeran($s, $user, $t['peran']);
    //         $this->periksaPemisahanTugas($s, $user, $t['tahap']);

    //         $final = $t['maju'] === SpjStatus::Final;
    //         if ($final) {
    //             $this->pagu->realisasi($s); // reserved -> realisasi_final
    //         }
    //         $s->status = $t['maju'];
    //         $s->save();

    //         $this->catat($s, $t['tahap'], $final ? 'disetujui' : 'diverifikasi', $user, $catatan);

    //         return $s->refresh();
    //     });
    // }

    public function maju(Spj $spj, User $user, ?string $catatan = null): Spj
    {
        return DB::transaction(function () use ($spj, $user, $catatan) {
            $s = $this->kunci($spj);
            $t = self::TAHAP[$s->status->value] ?? throw new SpjException('SPJ ini tidak sedang menunggu verifikasi atau persetujuan.');

            $this->periksaPeran($s, $user, $t['peran']);
            $this->periksaPemisahanTugas($s, $user, $t['tahap']);

            $final = $t['maju'] === SpjStatus::Final;
            
            if ($final) {
                // Jika sudah final, pindahkan dari reserved ke realisasi_final
                $this->pagu->realisasi($s); 
            } elseif ($s->status->value === 'diajukan') {
                // HANYA tahan pagu saat pertama kali diajukan (dari Bendahara ke PPK)
                $this->pagu->reserve($s);
            }
            
            $s->status = $t['maju'];
            $s->save();

            $this->catat($s, $t['tahap'], $final ? 'disetujui' : 'diverifikasi', $user, $catatan);

            return $s->refresh();
        });
    }

    /** Menolak SPJ pada tahap saat ini. Catatan wajib. Pagu yang ditahan dilepas. */
    public function kembalikan(Spj $spj, User $user, ?string $catatan): Spj
    {
        $catatan = $this->wajibCatatan($catatan, 'Catatan wajib diisi agar pembuat SPJ tahu apa yang harus diperbaiki.');

        return DB::transaction(function () use ($spj, $user, $catatan) {
            $s = $this->kunci($spj);
            $t = self::TAHAP[$s->status->value] ?? throw new SpjException('SPJ ini tidak sedang menunggu verifikasi atau persetujuan.');

            $this->periksaPeran($s, $user, $t['peran']);

            $this->pagu->release($s);
            $s->status = SpjStatus::Dikembalikan;
            $s->save();

            $this->catat($s, $t['tahap'], 'dikembalikan', $user, $catatan);

            return $s->refresh();
        });
    }

    /**
     * Membatalkan SPJ. SPJ final hanya oleh PA (realisasi dikembalikan).
     * SPJ yang dikembalikan dan tidak akan diperbaiki boleh dibatalkan oleh bendahara atau PA.
     */
    public function batalkan(Spj $spj, User $user, ?string $catatan): Spj
    {
        $catatan = $this->wajibCatatan($catatan, 'Alasan pembatalan wajib diisi.');

        return DB::transaction(function () use ($spj, $user, $catatan) {
            $s = $this->kunci($spj);

            if ($s->status === SpjStatus::Final) {
                $this->periksaPeran($s, $user, 'pa');
                $this->pagu->batalkanRealisasi($s);
            } elseif ($s->status === SpjStatus::Dikembalikan) {
                if (! $this->sesuai($s, $user, 'bendahara') && ! $this->sesuai($s, $user, 'pa')) {
                    throw new SpjException('Anda tidak berwenang membatalkan SPJ ini.');
                }
            } else {
                throw new SpjException('Hanya SPJ final atau SPJ yang dikembalikan yang bisa dibatalkan.');
            }

            $s->status = SpjStatus::Dibatalkan;
            $s->save();

            $this->catat($s, 'pembatalan', 'dibatalkan', $user, $catatan);

            return $s->refresh();
        });
    }

    // ------------------------------------------------------------------ internal

    private function kunci(Spj $spj): Spj
    {
        return Spj::withoutGlobalScopes()->lockForUpdate()->findOrFail($spj->id);
    }

    private function sesuai(Spj $spj, User $user, string $peran): bool
    {
        return $user->opd_id !== null && (int) $user->opd_id === (int) $spj->opd_id && $user->hasRole($peran);
    }

    private function periksaPeran(Spj $spj, User $user, string $peran): void
    {
        if (! $this->sesuai($spj, $user, $peran)) {
            throw new SpjException('Anda tidak berwenang memproses SPJ ini pada tahap ini.');
        }
    }

    private function periksaPemisahanTugas(Spj $spj, User $user, string $tahap): void
    {
        $mulai = (int) SpjApproval::where('spj_id', $spj->id)->where('aksi', 'diajukan')->max('id');
        $pengaju = $mulai ? (int) SpjApproval::whereKey($mulai)->value('user_id') : null;

        if (in_array($tahap, ['ppk_skpd', 'pa'], true) && $pengaju === (int) $user->id) {
            throw new SpjException('Anda mengajukan SPJ ini, jadi tidak boleh memproses tahap ini. Minta pejabat lain.');
        }

        $sudahDiTahapLain = SpjApproval::where('spj_id', $spj->id)->where('id', '>', $mulai)
            ->whereIn('aksi', ['diverifikasi', 'disetujui'])->where('user_id', $user->id)->where('tahap', '!=', $tahap)->exists();
        if ($sudahDiTahapLain) {
            throw new SpjException('Anda sudah memproses SPJ ini pada tahap sebelumnya. Tahap ini harus dikerjakan orang lain.');
        }
    }

    private function wajibCatatan(?string $catatan, string $pesan): string
    {
        $catatan = trim((string) $catatan);
        if ($catatan === '') {
            throw new SpjException($pesan);
        }

        return $catatan;
    }

    private function catat(Spj $spj, string $tahap, string $aksi, User $user, ?string $catatan): void
    {
        SpjApproval::create([
            'spj_id' => $spj->id, 'tahap' => $tahap, 'aksi' => $aksi, 'user_id' => $user->id,
            'catatan' => ($catatan !== null && trim($catatan) !== '') ? trim($catatan) : null,
        ]);
    }
}