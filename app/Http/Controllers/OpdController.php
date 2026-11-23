<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Support\PesanValidasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

/** Hanya superadmin (dijaga di route). */
class OpdController extends Controller
{
    public function index(): View
    {
        return view('opd.index');
    }

    /** Sumber data tabel server-side: paginasi, pencarian, dan urutan dikerjakan di database. */
    public function data(): JsonResponse
    {
        return DataTables::eloquent(Opd::query())
            ->addColumn('logo', fn (Opd $o) => $o->logo_path
                ? '<img src="' . e(Storage::disk('public')->url($o->logo_path)) . '" alt="Logo" height="28" class="rounded shadow-sm">'
                : '<span class="badge bg-secondary text-opacity-50">Belum ada</span>')
            
            // GANTI BAGIAN AKSI INI
            ->addColumn('aksi', function (Opd $o) {
                $edit = '<a href="#" 
                    class="btn-aksi-ikon btn-edit" 
                    title="Ubah Data"
                    data-id="' . $o->id . '"
                    data-kode="' . e($o->kode) . '"
                    data-nama="' . e($o->nama) . '"
                    data-singkatan="' . e($o->singkatan) . '"
                    data-alamat="' . e($o->alamat) . '"
                ><i class="bi bi-pencil-square"></i></a>';

                $hapus = '<form action="' . e(route('opd.destroy', $o)) . '" method="POST" class="d-inline form-hapus">
                    ' . csrf_field() . method_field('DELETE') . '
                    <button type="submit" class="btn-aksi-ikon btn-hapus" title="Hapus Data"><i class="bi bi-trash3"></i></button>
                </form>';

                return '<div class="d-flex gap-2 justify-content-end">' . $edit . $hapus . '</div>';
            })
            ->rawColumns(['logo', 'aksi'])
            ->toJson();
    }

    // TAMBAHKAN METHOD INI
    public function destroy(Opd $opd): RedirectResponse
    {
        if ($opd->logo_path) {
            Storage::disk('public')->delete($opd->logo_path);
        }
        $opd->delete();

        return redirect()->route('opd.index')->with('sukses', "OPD {$opd->nama} berhasil dihapus.");
    }

    public function create(): View
    {
        return view('opd.form', ['opd' => new Opd]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request);

        $opd = Opd::create(collect($data)->except('logo')->all());
        if ($request->hasFile('logo')) {
            $opd->update(['logo_path' => $request->file('logo')->store('logo-opd', 'public')]);
        }

        return redirect()->route('opd.index')->with('sukses', "OPD {$opd->nama} ditambahkan.");
    }

    public function edit(Opd $opd): View
    {
        return view('opd.form', ['opd' => $opd]);
    }

    public function update(Request $request, Opd $opd): RedirectResponse
    {
        $data = $this->validasi($request, $opd);

        $opd->update(collect($data)->except('logo')->all());
        if ($request->hasFile('logo')) {
            if ($opd->logo_path) {
                Storage::disk('public')->delete($opd->logo_path);
            }
            $opd->update(['logo_path' => $request->file('logo')->store('logo-opd', 'public')]);
        }

        return redirect()->route('opd.index')->with('sukses', "OPD {$opd->nama} diperbarui.");
    }

    private function validasi(Request $request, ?Opd $opd = null): array
    {
        $request->merge([
            'kode' => preg_replace('/\s+/', '', (string) $request->input('kode')),
            'singkatan' => strtoupper(preg_replace('/\s+/', '', (string) $request->input('singkatan'))),
        ]);

        return $request->validate([
            'kode' => ['required', 'regex:/^\d(\.\d+)*$/', 'max:40', Rule::unique('opd', 'kode')->ignore($opd?->id)],
            'nama' => ['required', 'string', 'max:255'],
            'singkatan' => ['required', 'regex:/^[A-Z0-9\-]+$/', 'max:30', Rule::unique('opd', 'singkatan')->ignore($opd?->id)],
            'alamat' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
        ], PesanValidasi::UMUM, [
            'kode' => 'Kode OPD', 'nama' => 'Nama OPD', 'singkatan' => 'Singkatan', 'alamat' => 'Alamat', 'logo' => 'Logo',
        ]);
    }
}