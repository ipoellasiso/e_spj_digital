<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\KonteksOpd;
use App\Models\Spj;
use App\Services\SpjWorkflowService;
use App\Support\Uang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

/** Antrian SPJ yang menunggu tindakan pengguna yang sedang login, menurut perannya. */
class SpjAntrianController extends Controller
{
    use KonteksOpd;

    public function __construct(private SpjWorkflowService $workflow)
    {
    }

    public function index(Request $request): View
    {
        $tahun = $this->tahunAnggaran($request);

        return view('master.index', [
            'judul' => 'Menunggu tindakan Anda', 'nav' => 'verifikasi',
            'deskripsi' => "SPJ tahun anggaran {$tahun} yang menunggu verifikasi atau persetujuan Anda. Buka SPJ untuk memeriksa lalu memverifikasi, menyetujui, atau mengembalikannya.",
            'urlData' => route('spj.antrian.data', ['tahun' => $tahun]),
            'urutAwal' => 1, 'urutArah' => 'asc',
            'kolom' => [
                ['data' => 'nomor_urut', 'judul' => 'Nomor', 'kelas' => 'angka'],
                ['data' => 'tanggal', 'judul' => 'Tanggal', 'cari' => false],
                ['data' => 'uraian', 'judul' => 'Uraian'],
                ['data' => 'rekening', 'judul' => 'Rekening', 'kelas' => 'angka', 'cari' => false, 'urut' => false],
                ['data' => 'total', 'judul' => 'Jumlah', 'kelas' => 'text-end angka', 'cari' => false],
                ['data' => 'status', 'judul' => 'Tahap', 'cari' => false],
            ],
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $me = $request->user();
        $status = $this->workflow->statusMenunggu($me);

        $query = Spj::query()->with('anggaranRekening.rekening:id,kode')
            ->where('spj.tahun', $this->tahunAnggaran($request))
            ->when($status, fn ($q) => $q->whereIn('spj.status', $status), fn ($q) => $q->whereRaw('1 = 0'));

        return DataTables::eloquent($query)
            ->editColumn('nomor_urut', fn (Spj $s) => $s->nomor ?? '-')
            ->editColumn('tanggal', fn (Spj $s) => $s->tanggal->format('d/m/Y'))
            ->addColumn('rekening', fn (Spj $s) => $s->anggaranRekening?->rekening?->kode)
            ->editColumn('total', fn (Spj $s) => Uang::rupiah(Uang::sen($s->total)))
            ->editColumn('status', fn (Spj $s) => '<span class="st ' . $s->status->badge() . '">' . e($s->status->label()) . '</span>')
            ->addColumn('aksi', fn (Spj $s) => '<a href="' . e(route('spj.show', $s)) . '" class="btn btn-sm btn-kuning">Tinjau</a>')
            ->rawColumns(['status', 'aksi'])
            ->toJson();
    }
}