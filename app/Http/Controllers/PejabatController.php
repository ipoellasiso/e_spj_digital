<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PilihOpd;
use App\Models\PejabatOpd;
use App\Support\Nip;
use App\Support\PesanValidasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

/** Pejabat penandatangan dokumen. Superadmin dan admin OPD (dijaga di route). */
class PejabatController extends Controller
{
    use PilihOpd;

    public function index(Request $request): View
    {
        return view('master.index', [
            'judul' => 'Pejabat penandatangan', 'nav' => 'pejabat',
            'deskripsi' => 'Nama dan jabatan yang tercetak di nota pesanan, berita acara, dan kwitansi. Data disalin saat dokumen dicetak, jadi pergantian pejabat tidak mengubah dokumen lama.',
            'urlData' => route('pejabat.data'), 
            'urlBaru' => route('pejabat.create'), // Tetap biarkan, view akan mengabaikan dan pakai modal
            'labelBaru' => 'Tambah pejabat',
            'kolom' => [
                ['data' => 'jabatan', 'judul' => 'Jabatan'],
                ['data' => 'nama', 'judul' => 'Nama'],
                ['data' => 'nip', 'judul' => 'NIP', 'kelas' => 'angka'],
                ['data' => 'opd', 'judul' => 'OPD', 'cari' => false, 'urut' => false],
                ['data' => 'periode', 'judul' => 'Masa tugas', 'cari' => false, 'urut' => false],
                ['data' => 'status', 'judul' => 'Status', 'cari' => false, 'urut' => false],
            ],
            // TAMBAHAN: Kirim daftar OPD untuk Modal Tambah
            'daftarOpd' => $this->daftarOpd($request),
        ]);
    }

    public function data(): JsonResponse
    {
        return DataTables::eloquent(PejabatOpd::query()->with('opd:id,nama'))
            ->editColumn('nip', fn (PejabatOpd $p) => Nip::format($p->nip))
            ->addColumn('opd', fn (PejabatOpd $p) => $p->opd?->nama)
            ->addColumn('periode', fn (PejabatOpd $p) => ($p->aktif_dari?->format('d/m/Y') ?? 'awal') . ' sampai ' . ($p->aktif_sampai?->format('d/m/Y') ?? 'sekarang'))
            ->addColumn('status', fn (PejabatOpd $p) => $this->aktif($p)
                ? '<span class="st st-verif">Aktif</span>' : '<span class="st st-draft">Tidak aktif</span>')
            
                        // UBAH BAGIAN INI
            ->addColumn('aksi', function (PejabatOpd $p) {
                // Tombol Edit
                $edit = '<a href="#" 
                    class="btn-aksi-ikon btn-edit" 
                    title="Ubah Data"
                    data-id="' . $p->id . '"
                    data-jabatan="' . e($p->jabatan) . '"
                    data-nama="' . e($p->nama) . '"
                    data-nip="' . e($p->nip) . '"
                    data-aktif_dari="' . e($p->aktif_dari?->format('Y-m-d')) . '"
                    data-aktif_sampai="' . e($p->aktif_sampai?->format('Y-m-d')) . '"
                ><i class="bi bi-pencil-square"></i></a>';

                // Tombol Hapus
                $hapus = '<form action="' . e(route('pejabat.destroy', $p)) . '" method="POST" class="d-inline form-hapus">
                    ' . csrf_field() . method_field('DELETE') . '
                    <button type="submit" class="btn-aksi-ikon btn-hapus" title="Hapus Data"><i class="bi bi-trash3"></i></button>
                </form>';

                // Gabungkan
                return '<div class="d-flex gap-2 justify-content-end">' . $edit . $hapus . '</div>';
            })
            ->rawColumns(['status', 'aksi'])
            ->toJson();
    }

    public function create(Request $request): View
    {
        // Method ini tidak akan terpakai jika modal jalan, 
        // tapi dibiarkan jika user mengakses URL secara langsung.
        return view('pejabat.form', ['pejabat' => new PejabatOpd, 'daftarOpd' => $this->daftarOpd($request)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request, true);
        $pejabat = PejabatOpd::create(Arr::except($data, 'opd_id') + ['opd_id' => $this->opdUntukSimpan($request, $data)]);

        return redirect()->route('pejabat.index')->with('sukses', "{$pejabat->jabatan} {$pejabat->nama} ditambahkan.");
    }

    public function edit(Request $request, PejabatOpd $pejabat): View
    {
        return view('pejabat.form', ['pejabat' => $pejabat, 'daftarOpd' => collect()]);
    }

    public function update(Request $request, PejabatOpd $pejabat): RedirectResponse
    {
        // Validasi: Karena kita menggunakan form modal (bukan method PUT langsung dari HTML),
        // Pastikan di view form edit kita sudah menambahkan @method('PUT') atau mengirim _method.
        $data = $this->validasi($request, false);
        
        // Bersihkan opd_id jika user bukan superadmin agar tidak error
        if (!$request->user()->hasRole('superadmin') && $request->user()->hasRole('admin')) {
             unset($data['opd_id']);
        }
        
        $pejabat->update(Arr::except($data, 'opd_id'));

        return redirect()->route('pejabat.index')->with('sukses', "{$pejabat->jabatan} {$pejabat->nama} diperbarui.");
    }

    private function aktif(PejabatOpd $p): bool
    {
        $hariIni = now()->startOfDay();

        return ($p->aktif_dari === null || $p->aktif_dari->lte($hariIni))
            && ($p->aktif_sampai === null || $p->aktif_sampai->gte($hariIni));
    }

    private function validasi(Request $request, bool $baru): array
    {
        $request->merge(['nip' => Nip::bersihkan($request->input('nip'))]);

        return $request->validate([
            'jabatan' => ['required', Rule::in(PejabatOpd::JABATAN)],
            'nama' => ['required', 'string', 'max:255'],
            'nip' => ['nullable', 'regex:/^\d{18}$/'],
            'aktif_dari' => ['nullable', 'date'],
            'aktif_sampai' => ['nullable', 'date', 'after_or_equal:aktif_dari'],
        ] + ($baru ? $this->aturanOpd($request) : []), PesanValidasi::UMUM, [
            'jabatan' => 'Jabatan', 'nama' => 'Nama', 'nip' => 'NIP', 'opd_id' => 'OPD',
            'aktif_dari' => 'Tanggal mulai', 'aktif_sampai' => 'Tanggal selesai',
        ]);
    }

    public function destroy(PejabatOpd $pejabat): RedirectResponse
    {
        $pejabat->delete();
        return redirect()->route('pejabat.index')->with('sukses', 'Pejabat berhasil dihapus.');
    }
}