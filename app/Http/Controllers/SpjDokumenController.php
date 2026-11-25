<?php

namespace App\Http\Controllers;

use App\Models\Spj;
use App\Models\SubKegiatan;
use App\Models\User;
use App\Services\DokumenSpjService;
use App\Support\AksesSubKegiatan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

/** Cetak dokumen SPJ (PDF). Semua peran yang boleh melihat SPJ; PPTK hanya sub kegiatannya. */
class SpjDokumenController extends Controller
{
    public function __construct(private DokumenSpjService $dokumen)
    {
    }

    public function satu(Request $request, Spj $spj, string $jenis): Response
    {
        abort_unless(in_array($jenis, $this->dokumen->slugUntuk($spj), true), 404);

        return $this->keluarkan($request, $spj, [$jenis], $jenis);
    }

    public function semua(Request $request, Spj $spj): Response
    {
        return $this->keluarkan($request, $spj, $this->dokumen->slugUntuk($spj), 'paket-dokumen');
    }

    /** Hasil pemeriksaan barang yang tercetak di BA Pemeriksaan (Baik atau Kurang Baik). */
    public function pemeriksaan(Request $request, Spj $spj): RedirectResponse
    {
        $this->pastikanBolehLihat($request->user(), $spj);
        abort_unless(in_array('ba-pemeriksaan', $this->dokumen->slugUntuk($spj), true) && $this->dokumen->bisaDicetak($spj) && $spj->status->value !== 'final', 422, 'Hasil pemeriksaan hanya bisa diubah selama SPJ belum final.');

        $data = $request->validate(['hasil' => ['required', Rule::in(['baik', 'kurang_baik'])]]);
        $this->dokumen->ubahHasilPemeriksaan($spj, $data['hasil']);

        return redirect()->route('spj.show', $spj)->with('sukses', 'Hasil pemeriksaan barang disimpan.');
    }

    private function keluarkan(Request $request, Spj $spj, array $slug, string $nama): Response
    {
        $this->pastikanBolehLihat($request->user(), $spj);
        abort_unless($this->dokumen->bisaDicetak($spj), 422, 'Dokumen hanya tersedia setelah SPJ diajukan dan sebelum dibatalkan atau dikembalikan.');

        $orientasi = collect($slug)->map(fn ($x) => DokumenSpjService::orientasi($x))->unique();
        abort_if($orientasi->count() > 1, 422, 'Dokumen dengan orientasi kertas berbeda dicetak terpisah.');

        $halaman = $this->dokumen->halaman($spj, $slug);
        $berkas = $this->dokumen->namaBerkas($spj, $nama, $halaman[0]['data']['d']);
        $pdf = Pdf::loadView('dokumen.satu', ['halaman' => $halaman])->setPaper('a4', $orientasi->first());

        return $request->boolean('unduh') ? $pdf->download($berkas) : $pdf->stream($berkas);
    }

    private function pastikanBolehLihat(User $me, Spj $spj): void
    {
        $batas = AksesSubKegiatan::batasan($me, $spj->tahun);
        if ($batas === null) {
            return;
        }
        $kode = SubKegiatan::withoutGlobalScopes()->whereKey($spj->anggaranRekening()->value('sub_kegiatan_id'))->value('kode');
        abort_unless(in_array($kode, $batas, true), 403, 'SPJ ini bukan dari sub kegiatan yang ditugaskan kepada Anda.');
    }
}