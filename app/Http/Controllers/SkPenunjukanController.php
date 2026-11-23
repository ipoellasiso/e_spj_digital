<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PilihOpd;
use App\Models\SkPenunjukan;
use App\Support\PesanValidasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

/** SK penunjukan pejabat. Nomor, tanggal, dan penerbitnya dikutip otomatis di berita acara. */
class SkPenunjukanController extends Controller
{
    use PilihOpd;

    public const JENIS = [
        'Penunjukan Pejabat Pengadaan', 'Penunjukan Pejabat Pembuat Komitmen', 'Penunjukan Pengguna Anggaran/KPA',
        'Penunjukan PPTK', 'Penunjukan Bendahara Pengeluaran', 'Penunjukan Pengurus Barang', 'Lainnya',
    ];

    public function index(Request $request): View
    {
        return view('master.index', [
            'judul' => 'SK penunjukan', 'nav' => 'sk',
            'deskripsi' => 'Surat keputusan yang dikutip di berita acara (nomor, tanggal, dan penerbitnya).',
            'urlData' => route('sk.data'), 'urlBaru' => route('sk.create'), 'labelBaru' => 'Tambah SK',
            'urutAwal' => 0, 'urutArah' => 'desc',
            'kolom' => [
                ['data' => 'tahun', 'judul' => 'Tahun', 'kelas' => 'angka'],
                ['data' => 'jenis', 'judul' => 'Jenis'],
                ['data' => 'nomor', 'judul' => 'Nomor'],
                ['data' => 'tanggal', 'judul' => 'Tanggal', 'cari' => false],
                ['data' => 'penerbit', 'judul' => 'Penerbit'],
                ['data' => 'opd', 'judul' => 'OPD', 'cari' => false, 'urut' => false],
            ],
            // Tambahkan ini agar modal Tambah bisa menampilkan pilihan OPD
            'daftarOpd' => $this->daftarOpd($request),
        ]);
    }

        public function data(): JsonResponse
    {
        return DataTables::eloquent(SkPenunjukan::query()->with('opd:id,nama'))
            ->editColumn('tanggal', fn (SkPenunjukan $s) => $s->tanggal->format('d/m/Y'))
            ->addColumn('opd', fn (SkPenunjukan $s) => $s->opd?->nama)
            
            ->addColumn('aksi', function (SkPenunjukan $s) {
                // Tombol Edit (Pensil)
                $edit = '<a href="#" class="btn-aksi-ikon btn-edit" title="Ubah Data"
                    data-id="' . $s->id . '"
                    data-jenis="' . e($s->jenis) . '"
                    data-nomor="' . e($s->nomor) . '"
                    data-tanggal="' . e($s->tanggal->format('Y-m-d')) . '"
                    data-penerbit="' . e($s->penerbit) . '"
                    data-tahun="' . e($s->tahun) . '"
                ><i class="bi bi-pencil-square"></i></a>';

                // Tombol Hapus (Tong Sampah)
                $hapus = '<form action="' . e(route('sk.destroy', $s)) . '" method="POST" class="d-inline form-hapus">
                    ' . csrf_field() . method_field('DELETE') . '
                    <button type="submit" class="btn-aksi-ikon btn-hapus" title="Hapus Data"><i class="bi bi-trash3"></i></button>
                </form>';

                // Gabungkan dalam satu flexbox
                return '<div class="d-flex gap-2 justify-content-end">' . $edit . $hapus . '</div>';
            })
            ->rawColumns(['aksi'])
            ->toJson();
    }

    public function create(Request $request): View
    {
        return view('sk.form', ['sk' => new SkPenunjukan(['tahun' => now()->year]), 'daftarOpd' => $this->daftarOpd($request), 'jenis' => self::JENIS]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request, true);
        SkPenunjukan::create(Arr::except($data, 'opd_id') + ['opd_id' => $this->opdUntukSimpan($request, $data)]);

        return redirect()->route('sk.index')->with('sukses', 'SK penunjukan ditambahkan.');
    }

    public function edit(SkPenunjukan $sk): View
    {
        return view('sk.form', ['sk' => $sk, 'daftarOpd' => collect(), 'jenis' => self::JENIS]);
    }

    public function update(Request $request, SkPenunjukan $sk): RedirectResponse
    {
        $sk->update($this->validasi($request, false));

        return redirect()->route('sk.index')->with('sukses', 'SK penunjukan diperbarui.');
    }

    private function validasi(Request $request, bool $baru): array
    {
        return $request->validate([
            'jenis' => ['required', Rule::in(self::JENIS)],
            'nomor' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'penerbit' => ['required', 'string', 'max:255'],
            'tahun' => ['required', 'integer', 'between:2000,2100'],
        ] + ($baru ? $this->aturanOpd($request) : []), PesanValidasi::UMUM, [
            'jenis' => 'Jenis', 'nomor' => 'Nomor SK', 'tanggal' => 'Tanggal SK', 'penerbit' => 'Penerbit',
            'tahun' => 'Tahun', 'opd_id' => 'OPD',
        ]);
    }

        public function destroy(SkPenunjukan $sk): RedirectResponse
    {
        $sk->delete();

        return redirect()->route('sk.index')->with('sukses', 'SK penunjukan berhasil dihapus.');
    }
    
}