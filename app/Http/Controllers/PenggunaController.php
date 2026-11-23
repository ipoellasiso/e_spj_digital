<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use App\Models\User;
use App\Support\PesanValidasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

/**
 * Superadmin: semua OPD dan semua peran.
 * Admin OPD: hanya pengguna di OPD-nya, dan hanya boleh memberi peran bendahara, pptk, ppk_skpd, pa.
 */
class PenggunaController extends Controller
{
    public const PERAN = [
        'superadmin' => 'Superadmin',
        'admin' => 'Admin OPD',
        'bendahara' => 'Bendahara pengeluaran',
        'pptk' => 'PPTK',
        'ppk_skpd' => 'PPK-SKPD',
        'pa' => 'Pengguna anggaran',
    ];

    public function index(): View
    {
        return view('pengguna.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = User::query()->with(['opd:id,nama', 'roles:id,name'])
            ->when(! $request->user()->isSuperadmin(), fn ($q) => $q->where('opd_id', $request->user()->opd_id));

        return DataTables::eloquent($query)
            ->addColumn('opd', fn (User $u) => $u->opd?->nama ?? 'Semua OPD')
            ->addColumn('peran', fn (User $u) => $u->roles->pluck('name')->map(fn ($r) => self::PERAN[$r] ?? $r)->implode(', '))
            
            // UBAH TAMPILAN STATUS JADI BADGE
            ->editColumn('aktif', fn (User $u) => $u->aktif
                ? '<span class="badge-status">Aktif</span>' : '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill px-3">Nonaktif</span>')
            
                        ->addColumn('aksi', function (User $u) {
                // Tombol Edit
                $edit = '<a href="#" 
                    class="btn-aksi-ikon btn-edit" 
                    title="Ubah Data"
                    data-id="' . $u->id . '"
                    data-name="' . e($u->name) . '"
                    data-email="' . e($u->email) . '"
                    data-nip="' . e($u->nip) . '"
                    data-opd_id="' . e($u->opd_id) . '"
                    data-aktif="' . ($u->aktif ? '1' : '0') . '"
                ><i class="bi bi-pencil-square"></i></a>';

                // Tombol Reset Password (BARU)
                $reset = '<a href="#" 
                    class="btn-aksi-ikon btn-reset-pass text-warning" 
                    title="Reset Sandi"
                    data-id="' . $u->id . '"
                    data-name="' . e($u->name) . '"
                ><i class="bi bi-key-fill"></i></a>';

                // Tombol Hapus
                $hapus = '<form action="' . e(route('pengguna.destroy', $u)) . '" method="POST" class="d-inline form-hapus">
                    ' . csrf_field() . method_field('DELETE') . '
                    <button type="submit" class="btn-aksi-ikon btn-hapus" title="Hapus Data"><i class="bi bi-trash3"></i></button>
                </form>';

                return '<div class="d-flex gap-2 justify-content-end">' . $edit . $reset . $hapus . '</div>';
            })
            ->addColumn('raw_roles', fn (User $u) => $u->roles->pluck('name')->toArray())
            ->rawColumns(['aktif', 'aksi'])
            ->toJson();
    }

    // TAMBAHKAN METHOD INI
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->pastikanBoleh($request->user(), $user);
        
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['hapus' => 'Anda tidak bisa menghapus akun sendiri.']);
        }

        $user->delete();
        return redirect()->route('pengguna.index')->with('sukses', "Pengguna {$user->name} berhasil dihapus.");
    }

    public function create(Request $request): View
    {
        return $this->form($request, new User(['aktif' => true]));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = new User;
        [$data, $peran, $opdId] = $this->validasi($request, $user);

        $user->fill([
            'name' => $data['name'], 'email' => $data['email'], 'nip' => $data['nip'] ?? null,
            'password' => Hash::make($data['password']),
            'opd_id' => $opdId, 'aktif' => $request->boolean('aktif'),
        ])->save();
        $user->syncRoles($peran);

        return redirect()->route('pengguna.index')->with('sukses', "Pengguna {$user->name} ditambahkan.");
    }

    public function edit(Request $request, User $user): View
    {
        $this->pastikanBoleh($request->user(), $user);

        return $this->form($request, $user);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->pastikanBoleh($request->user(), $user);
        [$data, $peran, $opdId] = $this->validasi($request, $user);

        $user->fill([
            'name' => $data['name'], 'email' => $data['email'], 'nip' => $data['nip'] ?? null,
            'opd_id' => $opdId, 'aktif' => $request->boolean('aktif'),
        ]);
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        // Peran yang tidak boleh dikelola pembuka form ini (mis. admin pada pengguna lain) dipertahankan.
        $dipertahankan = $user->getRoleNames()->diff($this->peranBoleh($request->user()))->all();
        $user->syncRoles(array_values(array_unique([...$peran, ...$dipertahankan])));

        return redirect()->route('pengguna.index')->with('sukses', "Pengguna {$user->name} diperbarui.");
    }

    /** @return string[] kode peran yang boleh diberikan oleh $me */
    private function peranBoleh(User $me): array
    {
        return $me->isSuperadmin()
            ? array_keys(self::PERAN)
            : ['bendahara', 'pptk', 'ppk_skpd', 'pa'];
    }

    private function pastikanBoleh(User $me, User $target): void
    {
        if ($me->isSuperadmin()) {
            return;
        }
        abort_unless(
            $me->opd_id !== null && $target->opd_id === $me->opd_id && ! $target->hasRole('superadmin'),
            403,
            'Anda tidak memiliki akses ke pengguna ini.'
        );
    }

    private function form(Request $request, User $user): View
    {
        $me = $request->user();
        $boleh = $this->peranBoleh($me);

        return view('pengguna.form', [
            'user' => $user,
            'peranBoleh' => array_intersect_key(self::PERAN, array_flip($boleh)),
            'peranTerpilih' => old('peran', $user->exists ? $user->getRoleNames()->all() : []),
            'daftarOpd' => $me->isSuperadmin() ? Opd::orderBy('nama')->get(['id', 'nama']) : collect(),
            'opdSendiri' => $me->isSuperadmin() ? null : $me->opd?->nama,
        ]);
    }

    /** @return array{0: array, 1: string[], 2: ?int} data tervalidasi, peran, opd_id final */
    private function validasi(Request $request, User $user): array
    {
        $me = $request->user();
        $boleh = $this->peranBoleh($me);
        $peranDikirim = (array) $request->input('peran', []);
        $pilihSuperadmin = in_array('superadmin', $peranDikirim, true);

        $request->merge(['nip' => preg_replace('/\s+/', '', (string) $request->input('nip')) ?: null]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'nip' => ['nullable', 'regex:/^\d{18}$/', Rule::unique('users', 'nip')->ignore($user->id)],
            'password' => [$user->exists ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'opd_id' => [Rule::requiredIf($me->isSuperadmin() && ! $pilihSuperadmin), 'nullable', 'exists:opd,id'],
            'peran' => ['required', 'array', 'min:1'],
            'peran.*' => [Rule::in($boleh)],
        ], PesanValidasi::UMUM, [
            'name' => 'Nama', 'email' => 'Email', 'nip' => 'NIP', 'password' => 'Kata sandi',
            'opd_id' => 'OPD', 'peran' => 'Peran', 'peran.*' => 'Peran',
        ]);

        // Jangan sampai mengunci diri sendiri.
        if ($user->exists && $user->id === $me->id) {
            if (! $request->boolean('aktif')) {
                throw ValidationException::withMessages(['aktif' => 'Anda tidak bisa menonaktifkan akun sendiri.']);
            }
            if ($me->isSuperadmin() && ! $pilihSuperadmin) {
                throw ValidationException::withMessages(['peran' => 'Anda tidak bisa mencabut peran superadmin dari akun sendiri.']);
            }
        }

        $opdId = $pilihSuperadmin ? null : ($me->isSuperadmin() ? (int) $data['opd_id'] : $me->opd_id);

        return [$data, $data['peran'], $opdId];
    }

    // TAMBAHKAN METHOD INI DI BAWAH METHOD UPDATE
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        // Pastikan admin berhak mengubah user ini
        $this->pastikanBoleh($request->user(), $user);

        $request->validate([
            'password_baru' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password_baru.required' => 'Kata sandi baru wajib diisi.',
            'password_baru.min' => 'Kata sandi baru minimal 8 karakter.',
            'password_baru.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user->update([
            'password' => Hash::make($request->password_baru),
        ]);

        return redirect()->route('pengguna.index')->with('sukses', "Kata sandi untuk {$user->name} berhasil direset.");
    }
}