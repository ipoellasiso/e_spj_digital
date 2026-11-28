<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\KonteksOpd;
use App\Models\Opd;
use App\Models\Spj;
use App\Services\LaporanExcel;
use App\Services\LaporanService;
use App\Support\AksesSubKegiatan;
use App\Support\TanggalIndo;
use App\Support\Uang;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;

/**
 * Laporan: buku kas umum (pengeluaran) dan realisasi anggaran. Semua peran boleh melihat;
 * data dibatasi ke OPD masing-masing dan PPTK hanya sub kegiatan yang ditugaskan.
 */
class LaporanController extends Controller
{
    use KonteksOpd;

    public function __construct(private LaporanService $laporan, private LaporanExcel $excel)
    {
    }

    // ------------------------------------------------------------------ BKU

    public function bku(Request $request): View
    {
        [$me, $opd, $tahun, $dari, $sampai, $batas] = $this->konteks($request);

        return view('laporan.bku', [
            'opd' => $opd, 'tahun' => $tahun, 'dari' => $dari, 'sampai' => $sampai,
            'ringkasan' => $opd ? $this->laporan->bkuRingkasan($this->laporan->bkuQuery($opd, $tahun, $dari, $sampai, $batas)) : null,
            'daftarOpd' => $me->isSuperadmin() ? Opd::orderBy('nama')->get(['id', 'nama']) : collect(),
            'kueri' => $request->query(),
        ]);
    }

    public function bkuData(Request $request): JsonResponse
    {
        [, $opd, $tahun, $dari, $sampai, $batas] = $this->konteks($request);
        $query = $opd ? $this->laporan->bkuQuery($opd, $tahun, $dari, $sampai, $batas) : Spj::query()->whereRaw('1 = 0');

        return DataTables::eloquent($query)
            ->editColumn('tanggal', fn (Spj $s) => $s->tanggal->format('d/m/Y'))
            ->editColumn('nomor_urut', fn (Spj $s) => $s->nomor ?? '-')
            ->addColumn('rekening', fn (Spj $s) => $s->anggaranRekening?->rekening?->kode)
            ->addColumn('sumber_dana', fn (Spj $s) => $s->sumberDana?->kode)
            ->editColumn('total', fn (Spj $s) => Uang::rupiah(Uang::sen($s->total)))
            ->addColumn('aksi', fn (Spj $s) => '<a href="' . e(route('spj.show', $s)) . '" class="btn btn-sm btn-outline-daun">Buka</a>')
            ->rawColumns(['aksi'])
            ->toJson();
    }

    public function bkuExcel(Request $request): StreamedResponse
    {
        [$opd, $tahun, $dari, $sampai, $batas] = $this->wajibOpd($request);
        $query = $this->laporan->bkuQuery($opd, $tahun, $dari, $sampai, $batas);

        return $this->excel->bku(
            $this->meta($opd, 'BUKU KAS UMUM (PENGELUARAN)', $this->periodeTeks($dari, $sampai), $this->namaBerkas('bku', $opd, 'xlsx', $dari, $sampai)),
            $this->laporan->bkuBaris($query), $this->laporan->bkuRingkasan($query)['total_sen'], $this->laporan->penandatangan($opd)
        );
    }

    public function bkuPdf(Request $request): Response
    {
        [$opd, $tahun, $dari, $sampai, $batas] = $this->wajibOpd($request);
        $query = $this->laporan->bkuQuery($opd, $tahun, $dari, $sampai, $batas);

        $pdf = Pdf::loadView('laporan.pdf.bku', [
            'meta' => $this->meta($opd, 'BUKU KAS UMUM (PENGELUARAN)', $this->periodeTeks($dari, $sampai), ''),
            'baris' => $this->laporan->bkuBaris($query), 'total_sen' => $this->laporan->bkuRingkasan($query)['total_sen'],
            'ttd' => $this->laporan->penandatangan($opd), 'tanggal_cetak' => TanggalIndo::panjang(now()),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream($this->namaBerkas('bku', $opd, 'pdf', $dari, $sampai));
    }

    // ------------------------------------------------------------ Realisasi

    public function realisasi(Request $request): View
    {
        [$me, $opd, $tahun, , $sampai, $batas] = $this->konteks($request);

        return view('laporan.realisasi', [
            'opd' => $opd, 'tahun' => $tahun, 'sampai' => $sampai,
            'hasil' => $opd ? $this->laporan->realisasi($opd, $tahun, $sampai, $batas) : null,
            'daftarOpd' => $me->isSuperadmin() ? Opd::orderBy('nama')->get(['id', 'nama']) : collect(),
            'kueri' => $request->query(),
        ]);
    }

    public function realisasiExcel(Request $request): StreamedResponse
    {
        [$opd, $tahun, , $sampai, $batas] = $this->wajibOpd($request);
        $hasil = $this->laporan->realisasi($opd, $tahun, $sampai, $batas);
        abort_unless($hasil['versi'], 404, 'DPA tahun ini belum diimpor.');

        return $this->excel->realisasi(
            $this->meta($opd, "REALISASI ANGGARAN BELANJA TAHUN {$tahun}", 'Sampai dengan ' . TanggalIndo::panjang($sampai), $this->namaBerkas('realisasi', $opd, 'xlsx', null, $sampai)),
            $hasil['baris'], $hasil['total'], $this->laporan->penandatangan($opd)
        );
    }

    public function realisasiPdf(Request $request): Response
    {
        [$opd, $tahun, , $sampai, $batas] = $this->wajibOpd($request);
        $hasil = $this->laporan->realisasi($opd, $tahun, $sampai, $batas);
        abort_unless($hasil['versi'], 404, 'DPA tahun ini belum diimpor.');

        $pdf = Pdf::loadView('laporan.pdf.realisasi', [
            'meta' => $this->meta($opd, "REALISASI ANGGARAN BELANJA TAHUN {$tahun}", 'Sampai dengan ' . TanggalIndo::panjang($sampai), ''),
            'baris' => $hasil['baris'], 'total' => $hasil['total'],
            'ttd' => $this->laporan->penandatangan($opd), 'tanggal_cetak' => TanggalIndo::panjang(now()),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream($this->namaBerkas('realisasi', $opd, 'pdf', null, $sampai));
    }

    // ------------------------------------------------------------- internal

    /** @return array{0: \App\Models\User, 1: ?Opd, 2: int, 3: Carbon, 4: Carbon, 5: ?array} */
    private function konteks(Request $request): array
    {
        $request->validate([
            'dari' => ['nullable', 'date'], 'sampai' => ['nullable', 'date'],
            'tahun' => ['nullable', 'integer', 'between:2000,2100'], 'opd' => ['nullable', 'integer'],
        ]);

        $me = $request->user();
        $tahun = $this->tahunAnggaran($request);
        $sekarang = now();
        $dalamTahunBerjalan = $sekarang->year === $tahun;

        $dari = $request->filled('dari')
            ? Carbon::parse($request->input('dari'))
            : ($dalamTahunBerjalan ? $sekarang->copy()->startOfMonth() : Carbon::create($tahun, 1, 1));
        $sampai = $request->filled('sampai')
            ? Carbon::parse($request->input('sampai'))
            : ($dalamTahunBerjalan ? $sekarang->copy() : Carbon::create($tahun, 12, 31));
        if ($sampai->lt($dari)) {
            $sampai = $dari->copy();
        }

        return [$me, $this->opdKonteks($request), $tahun, $dari->startOfDay(), $sampai->endOfDay(), AksesSubKegiatan::batasan($me, $tahun)];
    }

    /** @return array{0: Opd, 1: int, 2: Carbon, 3: Carbon, 4: ?array} */
    private function wajibOpd(Request $request): array
    {
        [, $opd, $tahun, $dari, $sampai, $batas] = $this->konteks($request);
        abort_unless($opd, 422, 'Pilih OPD terlebih dahulu.');

        return [$opd, $tahun, $dari, $sampai, $batas];
    }

    private function meta(Opd $opd, string $judul, string $periode, string $berkas): array
    {
        return ['pemda' => config('spj.pemda'), 'opd' => mb_strtoupper($opd->nama), 'judul' => $judul, 'periode' => $periode, 'berkas' => $berkas];
    }

    private function periodeTeks(Carbon $dari, Carbon $sampai): string
    {
        return 'Periode ' . TanggalIndo::panjang($dari) . ' sampai ' . TanggalIndo::panjang($sampai);
    }

    private function namaBerkas(string $jenis, Opd $opd, string $ekstensi, ?Carbon $dari, Carbon $sampai): string
    {
        $bagian = [$jenis, $opd->singkatan, $dari?->format('Ymd'), $sampai->format('Ymd')];

        return implode('-', array_filter($bagian)) . '.' . $ekstensi;
    }

    public function realisasiSumberDana(Request $request): View
    {
        [$me, $opd, $tahun, , $sampai, $batas] = $this->konteks($request);

        return view('laporan.realisasi-sumber-dana', [
            'nav' => 'realisasi-dana', // <-- TAMBAHKAN INI
            'opd' => $opd, 'tahun' => $tahun, 'sampai' => $sampai,
            'hasil' => $opd ? $this->laporan->realisasiPerSumberDana($opd, $tahun, $sampai, $batas) : null,
            'daftarOpd' => $me->isSuperadmin() ? Opd::orderBy('nama')->get(['id', 'nama']) : collect(),
            'kueri' => $request->query(),
        ]);
    }

        public function realisasiSumberDanaExcel(Request $request): StreamedResponse
    {
        [$opd, $tahun, , $sampai, $batas] = $this->wajibOpd($request);
        $hasil = $this->laporan->realisasiPerSumberDana($opd, $tahun, $sampai, $batas);
        abort_unless($hasil['versi'], 404, 'DPA tahun ini belum diimpor.');

        $namaBerkas = 'realisasi-sumber-dana-' . $opd->singkatan . '-' . $sampai->format('Ymd') . '.xls';

        return response()->streamDownload(function () use ($opd, $tahun, $sampai, $hasil) {
            echo view('laporan.excel.realisasi-sumber-dana', compact('opd', 'tahun', 'sampai', 'hasil'));
        }, $namaBerkas, [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }

    public function realisasiSumberDanaPdf(Request $request): Response
    {
        [$opd, $tahun, , $sampai, $batas] = $this->wajibOpd($request);
        $hasil = $this->laporan->realisasiPerSumberDana($opd, $tahun, $sampai, $batas);
        abort_unless($hasil['versi'], 404, 'DPA tahun ini belum diimpor.');

        $pdf = Pdf::loadView('laporan.pdf.realisasi-sumber-dana', [
            'opd' => $opd, 'tahun' => $tahun, 'sampai' => $sampai, 'hasil' => $hasil,
            'ttd' => $this->laporan->penandatangan($opd), 
            'tanggal_cetak' => TanggalIndo::panjang(now()),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('realisasi-sumber-dana-' . $opd->singkatan . '-' . $sampai->format('Ymd') . '.pdf');
    }
}