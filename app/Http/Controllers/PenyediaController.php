<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PilihOpd;
use App\Models\Penyedia;
use App\Support\PesanValidasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

/** Penyedia (rekanan). Superadmin, admin OPD, dan bendahara (dijaga di route). */
class PenyediaController extends Controller
{
    use PilihOpd;

    public function index(Request $request): View
    {
        return view('master.index', [
            'judul' => 'Penyedia', 'nav' => 'penyedia',
            'deskripsi' => 'Rekanan yang tercantum di nota pesanan dan kwitansi. Wajib diisi sebelum dokumen bisa dicetak.',
            'urlData' => route('penyedia.data'), 'urlBaru' => route('penyedia.create'), 'labelBaru' => 'Tambah penyedia',
            'kolom' => [
                ['data' => 'nama', 'judul' => 'Nama'],
                ['data' => 'alamat', 'judul' => 'Alamat'],
                ['data' => 'npwp', 'judul' => 'NPWP', 'kelas' => 'angka'],
                ['data' => 'rekening_bank', 'judul' => 'Rekening bank'],
                ['data' => 'opd', 'judul' => 'OPD', 'cari' => false, 'urut' => false],
            ],
            // Tambahkan ini agar Modal Tambah bisa menampilkan pilihan OPD
            'daftarOpd' => $this->daftarOpd($request),
        ]);
    }

        public function data(): JsonResponse
    {
        return DataTables::eloquent(Penyedia::query()->with('opd:id,nama'))
            ->addColumn('opd', fn (Penyedia $p) => $p->opd?->nama)
            ->addColumn('aksi', function (Penyedia $p) {
                // Tombol Edit
                $edit = '<a href="#" 
                    class="btn-aksi-ikon btn-edit" 
                    title="Ubah Data"
                    data-id="' . $p->id . '"
                    data-nama="' . e($p->nama) . '"
                    data-alamat="' . e($p->alamat) . '"
                    data-npwp="' . e($p->npwp) . '"
                    data-rekening_bank="' . e($p->rekening_bank) . '"
                ><i class="bi bi-pencil-square"></i></a>';

                // Tombol Hapus
                $hapus = '<form action="' . e(route('penyedia.destroy', $p)) . '" method="POST" class="d-inline form-hapus">
                    ' . csrf_field() . method_field('DELETE') . '
                    <button type="submit" class="btn-aksi-ikon btn-hapus" title="Hapus Data"><i class="bi bi-trash3"></i></button>
                </form>';

                return '<div class="d-flex gap-2 justify-content-end">' . $edit . $hapus . '</div>';
            })
            ->rawColumns(['aksi'])
            ->toJson();
    }

    public function destroy(Penyedia $penyedia): RedirectResponse
    {
        $penyedia->delete();
        return redirect()->route('penyedia.index')->with('sukses', "Penyedia {$penyedia->nama} berhasil dihapus.");
    }

    public function create(Request $request): View
    {
        return view('penyedia.form', ['penyedia' => new Penyedia, 'daftarOpd' => $this->daftarOpd($request)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request, true);
        $penyedia = Penyedia::create(Arr::except($data, 'opd_id') + ['opd_id' => $this->opdUntukSimpan($request, $data)]);

        return redirect()->route('penyedia.index')->with('sukses', "Penyedia {$penyedia->nama} ditambahkan.");
    }

    public function edit(Penyedia $penyedia): View
    {
        return view('penyedia.form', ['penyedia' => $penyedia, 'daftarOpd' => collect()]);
    }

    public function update(Request $request, Penyedia $penyedia): RedirectResponse
    {
        $penyedia->update($this->validasi($request, false));

        return redirect()->route('penyedia.index')->with('sukses', "Penyedia {$penyedia->nama} diperbarui.");
    }

    private function validasi(Request $request, bool $baru): array
    {
        $npwp = preg_replace('/\D/', '', (string) $request->input('npwp'));
        $request->merge(['npwp' => $npwp === '' ? null : $npwp]);

        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'npwp' => ['nullable', 'regex:/^\d{15,16}$/'],
            'rekening_bank' => ['nullable', 'string', 'max:255'],
        ] + ($baru ? $this->aturanOpd($request) : []), PesanValidasi::UMUM, [
            'nama' => 'Nama penyedia', 'alamat' => 'Alamat', 'npwp' => 'NPWP', 'rekening_bank' => 'Rekening bank', 'opd_id' => 'OPD',
        ]);
    }
}