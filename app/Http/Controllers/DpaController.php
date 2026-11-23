<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\KonteksOpd;
use App\Models\AnggaranRekening;
use App\Models\DpaVersi;
use App\Models\Opd;
use App\Models\SubKegiatan;
use App\Support\AksesSubKegiatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

/** DPA aktif dan sisa pagu. Semua peran boleh melihat; PPTK dibatasi ke sub kegiatan yang ditugaskan. */
class DpaController extends Controller
{
    use KonteksOpd;

    public function index(Request $request): View
    {
        $me = $request->user();
        $opd = $this->opdKonteks($request);
        $tahun = $this->tahunAnggaran($request);
        
        // --- LOGIKA BARU: Cek versi_id dari URL ---
        $versiId = $request->query('versi_id');
        
        if ($versiId) {
            $versi = DpaVersi::withoutGlobalScopes()
                ->where('opd_id', $opd->id ?? null)
                ->where('tahun', $tahun)
                ->where('id', $versiId)
                ->first();
        } else {
            $versi = $opd ? $this->versiAktif($opd, $tahun) : null;
        }
        
        $batas = AksesSubKegiatan::batasan($me, $tahun);

        $total = null;
        if ($versi) {
            $t = AnggaranRekening::withoutGlobalScopes()->where('dpa_versi_id', $versi->id)
                ->when($batas !== null, fn ($q) => $q->whereIn(
                    'sub_kegiatan_id',
                    SubKegiatan::withoutGlobalScopes()->where('dpa_versi_id', $versi->id)->whereIn('kode', $batas)->select('id')
                ))
                ->selectRaw('COALESCE(SUM(pagu),0) AS total_pagu, COALESCE(SUM(realisasi_final),0) AS total_final, COALESCE(SUM(reserved),0) AS total_proses')
                ->first();
                
            $pagu = (float) $t->total_pagu;
            $final = (float) $t->total_final;
            $proses = (float) $t->total_proses;
            
            $total = [
                'pagu' => $pagu, 'final' => $final, 'proses' => $proses, 'sisa' => $pagu - $final - $proses,
                'persen_final' => $pagu > 0 ? round($final / $pagu * 100, 1) : 0,
                'persen_proses' => $pagu > 0 ? round($proses / $pagu * 100, 1) : 0,
            ];
            $total['persen_sisa'] = round(max(0, 100 - $total['persen_final'] - $total['persen_proses']), 1);
        }

        $daftarVersi = DpaVersi::where('opd_id', $opd->id ?? null)
            ->where('tahun', $tahun)
            ->orderBy('jenis')
            ->orderBy('nomor_versi')
            ->get();

        return view('dpa.index', [
            'opd' => $opd, 'tahun' => $tahun, 'versi' => $versi, 'total' => $total,
            'daftarOpd' => $me->isSuperadmin() ? Opd::orderBy('nama')->get(['id', 'nama']) : collect(),
            'bolehImpor' => $me->hasAnyRole(['superadmin', 'admin', 'bendahara']),
            'terbatas' => $batas !== null,
            'daftarVersi' => $daftarVersi,
        ]);
    }

    /** Sub kegiatan versi aktif, server-side (paginasi, cari, urut di database). */
    public function data(Request $request): JsonResponse
    {
        $opd = $this->opdKonteks($request);
        $tahun = $this->tahunAnggaran($request);
        
        // --- LOGIKA BARU: Cek versi_id untuk DataTables ---
        $versiId = $request->query('versi_id');
        $versi = $versiId 
            ? DpaVersi::withoutGlobalScopes()->where('id', $versiId)->first()
            : ($opd ? $this->versiAktif($opd, $tahun) : null);

        $batas = AksesSubKegiatan::batasan($request->user(), $tahun);

        $query = SubKegiatan::withoutGlobalScopes()
            ->when($versi, fn ($q) => $q->where('dpa_versi_id', $versi->id), fn ($q) => $q->whereRaw('1 = 0'))
            ->when($batas !== null, fn ($q) => $q->whereIn('kode', $batas))
            ->withSum('anggaranRekening as realisasi', 'realisasi_final')
            ->withSum('anggaranRekening as reserved', 'reserved');

        return DataTables::eloquent($query)
            ->addColumn('sisa', fn (SubKegiatan $s) => number_format(
                (float) $s->pagu - (float) $s->realisasi - (float) $s->reserved, 2, '.', ''
            ))
            ->toJson();
    }

    /** Rekening belanja satu sub kegiatan, dimuat saat barisnya dibuka. */
    public function rekening(Request $request, SubKegiatan $sub): JsonResponse
    {
        $tahun = (int) DpaVersi::withoutGlobalScopes()->whereKey($sub->dpa_versi_id)->value('tahun');
        $batas = AksesSubKegiatan::batasan($request->user(), $tahun);
        abort_if($batas !== null && ! in_array($sub->kode, $batas, true), 403, 'Sub kegiatan ini tidak ditugaskan kepada Anda.');

        $rows = $sub->anggaranRekening()->with('rekening:id,kode,nama')->get()
            ->sortBy(fn (AnggaranRekening $a) => $a->rekening->kode)->values()
            ->map(fn (AnggaranRekening $a) => [
                'id' => $a->id,
                'kode' => $a->rekening->kode, 'nama' => $a->rekening->nama,
                'pagu' => $a->pagu, 'realisasi' => $a->realisasi_final, 'reserved' => $a->reserved, 'sisa' => $a->sisa,
            ]);

        return response()->json($rows);
    }
}