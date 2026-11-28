<?php

namespace App\Http\Controllers;

use App\Enums\SpjStatus;
use App\Models\AnggaranRekening;
use App\Models\Spj;
use App\Support\AksesSubKegiatan;
use App\Support\JenisBelanja;
use App\Support\Uang;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SpjRekapController extends Controller
{
    public function index(Request $request): View
    {
        [$tahun, $bulan] = $this->periode($request);
        $ringkasan = $this->ringkasan($request, $tahun, $bulan);

        return view('spj.rekap', [
            'nav' => 'spj-rekap',        // ← sesuai dengan @section('nav', ...)
            'tahun' => $tahun,
            'bulan' => $bulan,
            'ringkasan' => $ringkasan,
            'grandTotal' => $ringkasan->sum('total'),
            'daftarBulan' => $this->namaBulan(),
        ]);
    }

    public function detail(Request $request): View
    {
        [$tahun, $bulan] = $this->periode($request);
        $jenis = $request->query('jenis', 'Semua');

        $rows = $this->query($request, $tahun, $bulan)
            ->with(['anggaranRekening.rekening:id,kode', 'sumberDana:id,nama'])
            ->orderBy('tanggal')
            ->get()
            ->map(function (Spj $s) {
                $s->jenis_belanja = JenisBelanja::dariKode(
                    $s->anggaranRekening?->rekening?->kode,
                    $s->anggaranRekening?->rekening?->uraian
                );
                return $s;
            });

        if ($jenis !== 'Semua') {
            $rows = $rows->where('jenis_belanja', $jenis)->values();
        }

        return view('spj.rekap-detail', [
            'nav' => 'spj-rekap',
            'rows' => $rows,
            'jenis' => $jenis,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'total' => $rows->sum(fn ($s) => (float) $s->total),
        ]);
    }

    /** Export Excel sederhana (CSV) rekap. */
    public function ekspor(Request $request): StreamedResponse
    {
        [$tahun, $bulan] = $this->periode($request);
        $rows = $this->query($request, $tahun, $bulan)
            ->with(['anggaranRekening.rekening:id,kode'])
            ->orderBy('tanggal')->get();

        $nama = 'rekap-spj-' . $tahun . ($bulan ? '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) : '') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Tanggal', 'Nomor', 'Uraian', 'Kode Rekening', 'Jenis Belanja', 'Sumber Dana', 'Status', 'Total']);
            foreach ($rows as $s) {
                $kode = $s->anggaranRekening?->rekening?->kode;
                fputcsv($out, [
                    $s->tanggal?->format('d/m/Y'),
                    $s->nomor ?? '-',
                    $s->uraian,
                    $kode,
                    JenisBelanja::dariKode($kode),
                    $s->sumberDana?->nama,
                    $s->status->label(),
                    number_format((float) $s->total, 2, ',', '.'),
                ]);
            }
            fclose($out);
        }, $nama, ['Content-Type' => 'text/csv']);
    }

    // ------------------------------------------------------------- internal

    private function periode(Request $request): array
    {
        $tahun = (int) ($request->query('tahun') ?: date('Y'));
        $bulan = $request->query('bulan') ? (int) $request->query('bulan') : null;

        return [$tahun, $bulan];
    }

    private function query(Request $request, int $tahun, ?int $bulan)
    {
        $me = $request->user();
        $batas = AksesSubKegiatan::batasan($me, $tahun);

        return Spj::query()
            ->where('spj.tahun', $tahun)
            ->when($bulan, fn ($q) => $q->whereMonth('spj.tanggal', $bulan))
            // default: hanya yang sudah diajukan ke atas (bukan draft)
            ->when($request->query('status'), fn ($q, $s) => $q->where('spj.status', $s))
            ->when(! $request->query('status'), fn ($q) => $q->where('spj.status', '!=', SpjStatus::Draft->value))
            ->when($batas !== null, fn ($q) => $q->whereHas(
                'anggaranRekening.subKegiatan',
                fn ($s) => $s->whereIn('kode', $batas)
            ));
    }

    /** @return \Illuminate\Support\Collection<string, array{jumlah:int,total:float}> */
    private function ringkasan(Request $request, int $tahun, ?int $bulan)
    {
        $rows = $this->query($request, $tahun, $bulan)
            ->with(['anggaranRekening.rekening:id,kode'])
            ->get();

        return $rows->groupBy(fn (Spj $s) => JenisBelanja::dariKode(
                    $s->anggaranRekening?->rekening?->kode,
                    $s->anggaranRekening?->rekening?->uraian
                ))
            ->map(fn ($g) => [
                'jumlah' => $g->count(),
                'total' => (float) $g->sum('total'),
            ])
            ->sortKeys();
    }

    private function namaBulan(): array
    {
        return [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    }
}