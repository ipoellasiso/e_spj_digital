<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\KonteksOpd;
use App\Models\PptkSubKegiatan;
use App\Models\SubKegiatan;
use App\Models\User;
use App\Support\Nip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

/**
 * Penugasan PPTK ke sub kegiatan. Satu sub kegiatan hanya boleh punya satu PPTK per tahun.
 * Superadmin, admin OPD, dan bendahara (dijaga di route).
 */
class PptkController extends Controller
{
    use KonteksOpd;

    public function index(Request $request): View
    {
        $tahun = $this->tahunAnggaran($request);

        return view('master.index', [
            'judul' => 'Penugasan PPTK', 'nav' => 'pptk',
            'deskripsi' => "Tahun anggaran {$tahun}. PPTK hanya melihat dan membuat SPJ untuk sub kegiatan yang ditugaskan kepadanya. Satu sub kegiatan hanya punya satu PPTK.",
            'urlData' => route('pptk.data', ['tahun' => $tahun]),
            'kolom' => [
                ['data' => 'name', 'judul' => 'Nama'],
                ['data' => 'nip', 'judul' => 'NIP', 'kelas' => 'angka'],
                ['data' => 'opd', 'judul' => 'OPD', 'cari' => false, 'urut' => false],
                ['data' => 'jumlah_tugas', 'judul' => 'Sub kegiatan ditugaskan', 'kelas' => 'text-end angka', 'cari' => false],
            ],
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $me = $request->user();
        $tahun = $this->tahunAnggaran($request);

        $query = User::query()->role('pptk')->with('opd:id,nama')->select('users.*')
            ->addSelect(['jumlah_tugas' => PptkSubKegiatan::withoutGlobalScopes()->selectRaw('count(*)')
                ->whereColumn('pptk_sub_kegiatan.user_id', 'users.id')->where('pptk_sub_kegiatan.tahun', $tahun)])
            ->when(! $me->isSuperadmin(), fn ($q) => $q->where('users.opd_id', $me->opd_id));

        return DataTables::eloquent($query)
            ->editColumn('nip', fn (User $u) => Nip::format($u->nip))
            ->addColumn('opd', fn (User $u) => $u->opd?->nama)
            
            // UBAH BAGIAN INI: Pakai Ikon
            ->addColumn('aksi', function (User $u) use ($tahun) {
                $url = route('pptk.edit', [$u, 'tahun' => $tahun]);
                return '<a href="' . e($url) . '" class="btn-aksi-ikon text-primary" title="Atur Sub Kegiatan"><i class="bi bi-ui-checks"></i></a>';
            })
            
            ->rawColumns(['aksi'])
            ->toJson();
    }

    public function edit(Request $request, User $user): View
    {
        $this->pastikanPptk($request->user(), $user);
        $tahun = $this->tahunAnggaran($request);
        $versi = $user->opd ? $this->versiAktif($user->opd, $tahun) : null;

        $sub = collect();
        $terpilih = [];
        $dipakaiLain = [];
        if ($versi) {
            $sub = SubKegiatan::withoutGlobalScopes()->where('dpa_versi_id', $versi->id)->orderBy('kode')->get(['kode', 'uraian', 'pagu']);
            $terpilih = PptkSubKegiatan::withoutGlobalScopes()->where('user_id', $user->id)->where('tahun', $tahun)->pluck('kode_sub_kegiatan')->all();
            $lain = PptkSubKegiatan::withoutGlobalScopes()->where('opd_id', $user->opd_id)->where('tahun', $tahun)
                ->where('user_id', '!=', $user->id)->get(['user_id', 'kode_sub_kegiatan']);
            $nama = User::whereIn('id', $lain->pluck('user_id')->unique())->pluck('name', 'id');
            $dipakaiLain = $lain->mapWithKeys(fn ($r) => [$r->kode_sub_kegiatan => $nama[$r->user_id] ?? 'PPTK lain'])->all();
        }

        return view('pptk.edit', [
            'pptk' => $user, 'tahun' => $tahun, 'versi' => $versi, 'sub' => $sub,
            'terpilih' => old('sub', $terpilih), 'dipakaiLain' => $dipakaiLain,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->pastikanPptk($request->user(), $user);
        $tahun = (int) $request->input('tahun');
        $versi = $user->opd ? $this->versiAktif($user->opd, $tahun) : null;
        if (! $versi) {
            throw ValidationException::withMessages(['tahun' => "DPA tahun {$tahun} belum diimpor."]);
        }

        $kodeValid = SubKegiatan::withoutGlobalScopes()->where('dpa_versi_id', $versi->id)->pluck('kode')->all();
        $data = $request->validate([
            'tahun' => ['required', 'integer', 'between:2000,2100'],
            'sub' => ['nullable', 'array'],
            'sub.*' => ['string', Rule::in($kodeValid)],
        ], ['in' => 'Sub kegiatan :input tidak ada di DPA aktif.', 'between' => 'Tahun tidak valid.']);
        $kode = array_values(array_unique($data['sub'] ?? []));

        $dipakai = PptkSubKegiatan::withoutGlobalScopes()->where('opd_id', $user->opd_id)->where('tahun', $tahun)
            ->where('user_id', '!=', $user->id)->whereIn('kode_sub_kegiatan', $kode)->pluck('kode_sub_kegiatan')->all();
        if ($dipakai) {
            throw ValidationException::withMessages(['sub' => 'Sudah ditugaskan ke PPTK lain: ' . implode(', ', $dipakai) . '. Lepas dulu dari PPTK tersebut.']);
        }

        DB::transaction(function () use ($user, $tahun, $kode) {
            PptkSubKegiatan::withoutGlobalScopes()->where('user_id', $user->id)->where('tahun', $tahun)->delete();
            if ($kode) {
                PptkSubKegiatan::insert(array_map(fn ($k) => [
                    'opd_id' => $user->opd_id, 'tahun' => $tahun, 'user_id' => $user->id, 'kode_sub_kegiatan' => $k,
                ], $kode));
            }
        });

        return redirect()->route('pptk.index', ['tahun' => $tahun])
            ->with('sukses', "Penugasan {$user->name} diperbarui (" . count($kode) . ' sub kegiatan).');
    }

    private function pastikanPptk(User $me, User $target): void
    {
        abort_unless($target->hasRole('pptk'), 404);
        if (! $me->isSuperadmin()) {
            abort_unless($me->opd_id !== null && $target->opd_id === $me->opd_id, 403, 'Anda tidak memiliki akses ke pengguna ini.');
        }
    }
}