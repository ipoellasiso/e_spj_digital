<?php

namespace App\Http\Controllers;

use App\Enums\SpjStatus;
use App\Exceptions\SpjException;
use App\Models\Spj;
use App\Services\SpjWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Verifikasi, persetujuan, pengembalian, dan pembatalan SPJ. Bendahara, PPK-SKPD, dan PA (dijaga di route). */
class SpjProsesController extends Controller
{
    public function __construct(private SpjWorkflowService $workflow)
    {
    }

    public function __invoke(Request $request, Spj $spj): RedirectResponse
    {
        $data = $request->validate([
            'aksi' => ['required', Rule::in(['maju', 'kembalikan', 'batalkan'])],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        // try {
        //     $hasil = match ($data['aksi']) {
        //         'maju' => $this->workflow->maju($spj, $request->user(), $data['catatan'] ?? null),
        //         'kembalikan' => $this->workflow->kembalikan($spj, $request->user(), $data['catatan'] ?? null),
        //         'batalkan' => $this->workflow->batalkan($spj, $request->user(), $data['catatan'] ?? null),
        //     };
        // } catch (SpjException $e) {
        //     return redirect()->route('spj.show', $spj)->withErrors(['proses' => $e->getMessage()]);
        // }

        try {
            $hasil = match ($data['aksi']) {
                'maju' => $this->workflow->maju($spj, $request->user(), $data['catatan'] ?? null),
                'kembalikan' => $this->workflow->kembalikan($spj, $request->user(), $data['catatan'] ?? null),
                'batalkan' => $this->workflow->batalkan($spj, $request->user(), $data['catatan'] ?? null),
            };
        } catch (\Exception $e) { // Tangkap semua error
            return redirect()->route('spj.show', $spj)->withErrors(['proses' => $e->getMessage()]);
        }

        $pesan = match ($hasil->status) {
            SpjStatus::Final => 'SPJ disetujui dan menjadi final. Realisasi pagu tercatat.',
            SpjStatus::Dikembalikan => 'SPJ dikembalikan ke pembuatnya. Pagu yang ditahan sudah dilepas.',
            SpjStatus::Dibatalkan => 'SPJ dibatalkan.',
            default => 'SPJ diverifikasi dan diteruskan ke tahap berikutnya.',
        };

        return redirect()->route('spj.show', $spj)->with('sukses', $pesan);
    }
}