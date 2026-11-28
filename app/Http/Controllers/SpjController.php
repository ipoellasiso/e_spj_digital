<?php

namespace App\Http\Controllers;

use App\Enums\SpjStatus;
use App\Exceptions\SpjException;
use App\Http\Controllers\Concerns\KonteksOpd;
use App\Models\AnggaranRekening;
use App\Models\DpaVersi;
use App\Models\PejabatOpd;
use App\Models\Penyedia;
use App\Models\SubKegiatan;
use App\Models\SumberDana;
use App\Models\Spj;
use App\Models\User;
use App\Services\PaguService;
use App\Services\SpjService;
use App\Support\AksesSubKegiatan;
use App\Support\PesanValidasi;
use App\Support\Uang;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

/**
 * SPJ. Melihat: semua peran (PPTK hanya sub kegiatannya). Membuat dan mengubah: bendahara dan PPTK.
 */
class SpjController extends Controller
{
    use KonteksOpd;

    public function __construct(private SpjService $service, private PaguService $pagu)
    {
    }

    public function index(Request $request): View
    {
        $me = $request->user();
        $status = SpjStatus::tryFrom((string) $request->query('status'));

        $filter = [['label' => 'Semua', 'url' => route('spj.index'), 'aktif' => $status === null]];
        foreach (SpjStatus::cases() as $s) {
            $filter[] = ['label' => $s->label(), 'url' => route('spj.index', ['status' => $s->value]), 'aktif' => $status === $s];
        }

        return view('master.index', [
            'judul' => 'Daftar SPJ', 'nav' => 'spj',
            'deskripsi' => 'Tahun anggaran ' . $this->tahunAnggaran($request) . '. Nomor baru diberikan saat SPJ pertama kali diajukan.',
            'urlData' => route('spj.data', array_filter(['status' => $status?->value])),
            'filter' => $filter,
            'urutAwal' => 1, 'urutArah' => 'desc',
            'kolom' => [
                ['data' => 'nomor_urut', 'judul' => 'Nomor', 'kelas' => 'angka'],
                // Tambahkan di dalam array 'kolom' setelah 'nomor_urut'
                ['data' => 'nomor_spm', 'judul' => 'Nomor SPM', 'cari' => false],
                ['data' => 'tanggal', 'judul' => 'Tanggal', 'cari' => false],
                ['data' => 'uraian', 'judul' => 'Uraian'],
                ['data' => 'rekening', 'judul' => 'Rekening', 'kelas' => 'angka', 'cari' => false, 'urut' => false],
                ['data' => 'total', 'judul' => 'Jumlah', 'kelas' => 'text-end angka', 'cari' => false],
                ['data' => 'status', 'judul' => 'Status', 'cari' => false],
            ],
        ] + ($me->hasAnyRole(['bendahara', 'pptk']) ? ['urlBaru' => route('spj.create'), 'labelBaru' => 'Buat SPJ'] : []));
    }

    public function data(Request $request): JsonResponse
    {
        $tahun = $this->tahunAnggaran($request);
        $status = SpjStatus::tryFrom((string) $request->query('status'));
        $batas = AksesSubKegiatan::batasan($request->user(), $tahun);

        $query = Spj::query()->with('anggaranRekening.rekening:id,kode')->where('spj.tahun', $tahun)
            ->when($status, fn ($q) => $q->where('spj.status', $status->value))
            ->when($batas !== null, fn ($q) => $q->whereHas('anggaranRekening.subKegiatan', fn ($s) => $s->whereIn('kode', $batas)));

        // Cek apakah user boleh edit (Bendahara/PPTK)
        $bolehEdit = $request->user()->hasAnyRole(['bendahara', 'pptk']);

        return DataTables::eloquent($query)
            ->editColumn('nomor_urut', fn (Spj $s) => $s->nomor ?? '-')
            
            // --- KOLOM INPUT SPM INLINE ---
            ->editColumn('nomor_spm', function (Spj $s) use ($bolehEdit) {
                if ($bolehEdit) {
                    // Render Input text. Class 'input-spm' digunakan untuk trigger AJAX
                    return '<input type="text" class="form-control form-control-sm input-spm" 
                                data-id="'.$s->id.'" 
                                value="'.e($s->nomor_spm).'" 
                                placeholder="Ketik SPM...">';
                }
                // Jika role lain (misal PA), hanya bisa lihat
                return $s->nomor_spm ? '<span class="badge bg-success">'.e($s->nomor_spm).'</span>' : '-';
            })
            // -------------------------------

            ->editColumn('tanggal', fn (Spj $s) => $s->tanggal->format('d/m/Y'))
            ->addColumn('rekening', fn (Spj $s) => $s->anggaranRekening?->rekening?->kode)
            ->editColumn('total', fn (Spj $s) => Uang::rupiah(Uang::sen($s->total)))
            ->editColumn('status', fn (Spj $s) => '<span class="st ' . $s->status->badge() . '">' . e($s->status->label()) . '</span>')
            ->addColumn('aksi', fn (Spj $s) => '<a href="' . e(route('spj.show', $s)) . '" class="btn btn-sm btn-outline-daun">Buka</a>')
            
            // WAJIB: Tambahkan 'nomor_spm' ke rawColumns karena kita merender HTML (input)
            ->rawColumns(['status', 'aksi', 'nomor_spm'])
            ->addColumn('rekening', fn (Spj $s) => $s->kategori === 'gaji' ? 'Gaji (banyak rekening)' : $s->anggaranRekening?->rekening?->kode)
            ->toJson();
    }

    public function create(Request $request): View
    {
        return $this->tampilForm($request, new Spj(['tanggal' => now(), 'jenis' => 'GU']));
    }

    public function store(Request $request): RedirectResponse
    {
        [$data, $items] = $this->validasi($request, null);

        try {
            $spj = $this->service->simpan($data, $items, $request->user());
        } catch (SpjException $e) {
            return back()->withInput()->withErrors(['spj' => $e->getMessage()]);
        }

        return redirect()->route('spj.show', $spj)->with('sukses', 'SPJ disimpan sebagai draft. Periksa lalu ajukan bila sudah lengkap.');
    }

    public function show(Request $request, Spj $spj): View
    {
        $me = $request->user();
        $this->pastikanBolehLihat($me, $spj);
        $spj->load(['items', 'sumberDana', 'penyedia', 'pembuat', 'approval.user', 'anggaranRekening.rekening', 'anggaranRekening.subKegiatan']);

        $ids = array_filter($spj->only(array_keys(Spj::PEJABAT)));
        $ar = $spj->anggaranRekening;

        return view('spj.show', [
            'spj' => $spj,
            'pejabat' => PejabatOpd::withoutGlobalScopes()->whereIn('id', $ids)->get()->keyBy('id'),
            'sisaSen' => $this->pagu->sisaSen($ar),
            'bolehKelola' => $spj->status->bisaDiubah() && $this->bolehKelola($me, $spj),
            'paguGaji' => $spj->kategori === 'gaji' ? $this->pagu->posisi($spj) : null,
        ]);
    }

    public function edit(Request $request, Spj $spj): View|RedirectResponse
    {
        $this->pastikanBolehKelola($request->user(), $spj);
        if (! $spj->status->bisaDiubah()) {
            return redirect()->route('spj.show', $spj)->withErrors(['spj' => 'SPJ yang sudah diajukan tidak bisa diubah.']);
        }

        return $this->tampilForm($request, $spj->load('items', 'anggaranRekening'));
    }

    public function update(Request $request, Spj $spj): RedirectResponse
    {
        $this->pastikanBolehKelola($request->user(), $spj);
        [$data, $items] = $this->validasi($request, $spj);

        try {
            $this->service->simpan($data, $items, $request->user(), $spj);
        } catch (SpjException $e) {
            return back()->withInput()->withErrors(['spj' => $e->getMessage()]);
        }

        return redirect()->route('spj.show', $spj)->with('sukses', 'SPJ diperbarui.');
    }

    public function ajukan(Request $request, Spj $spj): RedirectResponse
    {
        $this->pastikanBolehKelola($request->user(), $spj);

        try {
            $spj = $this->service->ajukan($spj, $request->user());
        } catch (SpjException $e) {
            return redirect()->route('spj.show', $spj)->withErrors(['ajukan' => $e->getMessage()]);
        }

        return redirect()->route('spj.show', $spj)->with('sukses', "SPJ diajukan dengan nomor {$spj->nomor}. Pagu rekening sudah ditahan.");
    }

    public function destroy(Request $request, Spj $spj): RedirectResponse
    {
        $this->pastikanBolehKelola($request->user(), $spj);

        try {
            $this->service->hapus($spj);
        } catch (SpjException $e) {
            return redirect()->route('spj.show', $spj)->withErrors(['spj' => $e->getMessage()]);
        }

        return redirect()->route('spj.index')->with('sukses', 'Draft SPJ dihapus.');
    }

    public function updateSpm(Request $request, Spj $spj): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['bendahara', 'pptk']), 403);
        
        $request->validate(['nomor_spm' => ['nullable', 'string', 'max:100']]);
        $spj->update(['nomor_spm' => $request->nomor_spm]);

        return response()->json([
            'status' => 'success',
            'message' => 'Nomor SPM tersimpan.',
            'nomor_spm' => $spj->nomor_spm
        ]);
    }

    // ---------------------------------------------------------------- akses

    private function batasSub(User $me, Spj $spj): ?array
    {
        return AksesSubKegiatan::batasan($me, $spj->tahun);
    }

    private function subKodeSpj(Spj $spj): ?string
    {
        return $spj->anggaranRekening?->subKegiatan?->kode
            ?? SubKegiatan::withoutGlobalScopes()->whereKey($spj->anggaranRekening()->value('sub_kegiatan_id'))->value('kode');
    }

    private function pastikanBolehLihat(User $me, Spj $spj): void
    {
        $batas = $this->batasSub($me, $spj);
        abort_if($batas !== null && ! in_array($this->subKodeSpj($spj), $batas, true), 403, 'SPJ ini bukan dari sub kegiatan yang ditugaskan kepada Anda.');
    }

    private function bolehKelola(User $me, Spj $spj): bool
    {
        if (! $me->hasAnyRole(['bendahara', 'pptk'])) {
            return false;
        }
        $batas = $this->batasSub($me, $spj);

        return $batas === null || in_array($this->subKodeSpj($spj), $batas, true);
    }

    private function pastikanBolehKelola(User $me, Spj $spj): void
    {
        abort_unless($this->bolehKelola($me, $spj), 403, 'Anda tidak berwenang mengelola SPJ ini.');
    }

    // ----------------------------------------------------------------- form

    private function tampilForm(Request $request, Spj $spj): View
    {
        $me = $request->user();
        abort_unless($me->opd_id, 403, 'Akun Anda belum terhubung ke OPD.');

        $tahun = $spj->exists ? (int) $spj->tahun : $this->tahunAnggaran($request);
        $versi = $this->versiAktif($me->opd, $tahun);
        $batas = AksesSubKegiatan::batasan($me, $tahun);

        $sub = collect();
        if ($versi) {
            $sub = SubKegiatan::withoutGlobalScopes()->where('dpa_versi_id', $versi->id)
                ->when($batas !== null, fn ($q) => $q->whereIn('kode', $batas))->orderBy('kode')->get(['id', 'kode', 'uraian']);
        }

        // $aktif = PejabatOpd::where('opd_id', $me->opd_id)->aktif()->orderBy('nama')->get();
        $aktif = PejabatOpd::where('opd_id', $me->opd_id)
            ->where(function($q) {
                $q->whereNull('aktif_sampai')->orWhere('aktif_sampai', '>=', now());
            })
            ->orderBy('nama')
            ->get();
        $pilihan = [];
        foreach (Spj::PEJABAT as $kolom => [$label, $jabatan]) {
            $opsi = $aktif->whereIn('jabatan', $jabatan)->values();
            if ($spj->{$kolom} && ! $opsi->contains('id', $spj->{$kolom}) && ($lama = PejabatOpd::find($spj->{$kolom}))) {
                $opsi->push($lama);
            }
            $pilihan[$kolom] = ['label' => $label, 'opsi' => $opsi, 'default' => $opsi->count() === 1 ? $opsi->first()->id : null];
        }

        $items = $spj->exists
            ? $spj->items->map(fn ($i) => [
                'uraian' => $i->uraian, 'volume' => Uang::isianVolume($i->volume), 'satuan' => $i->satuan, 'harga_satuan' => Uang::isianHarga($i->harga_satuan),
            ])->all()
            : [['uraian' => '', 'volume' => '1', 'satuan' => '', 'harga_satuan' => '']];
        
        $gajiAwal = [];
            if (old('items')) {
                foreach ((array) old('items') as $it) {
                    if (! empty($it['anggaran_rekening_id'])) {
                        $gajiAwal[$it['anggaran_rekening_id']] = $it['harga_satuan'] ?? '';
                    }
                }
            } elseif ($spj->exists && $spj->kategori === 'gaji') {
                foreach ($spj->items as $i) {
                    $gajiAwal[$i->anggaran_rekening_id] = Uang::isianHarga($i->harga_satuan);
                }
            }

        return view('spj.form', [
            'spj' => $spj, 'tahun' => $tahun, 'versi' => $versi, 'sub' => $sub,
            // 'subTerpilih' => $spj->exists ? $spj->anggaranRekening?->sub_kegiatan_id : null,
            'subTerpilih' => old('sub', $spj->exists ? $spj->anggaranRekening?->sub_kegiatan_id : null),
            'sumberDana' => SumberDana::orderBy('kode')->get(),
            'penyedia' => Penyedia::where('opd_id', $me->opd_id)->orderBy('nama')->get(['id', 'nama']),
            'pilihanPejabat' => $pilihan,
            'items' => old('items', $items),
            'gajiAwal' => $gajiAwal,
        ]);
    }

    private function validasi(Request $request, ?Spj $spj): array
    {
        $me = $request->user();
        abort_unless($me->opd_id, 403, 'Akun Anda belum terhubung ke OPD.');
        $opdId = $me->opd_id;
        $gaji = $request->input('kategori') === 'gaji';

        if ($gaji) {
            // rekening yang nilainya dikosongkan dibuang
            $request->merge(['items' => collect((array) $request->input('items'))
                ->filter(fn ($i) => (Uang::dariInput((string) ($i['harga_satuan'] ?? '')) ?? 0) > 0)
                ->all()]);
        }

        $aturan = [
            'jenis' => ['required', Rule::in(array_keys(Spj::JENIS))],
            'tanggal' => ['required', 'date'],
            'anggaran_rekening_id' => $gaji
                ? ['nullable']
                : ['required', Rule::exists('anggaran_rekening', 'id')->where('opd_id', $opdId)],
            'sumber_dana_id' => ['required', 'exists:sumber_dana,id'],
            'uraian' => ['required', 'string', 'max:500'],
            'penyedia_id' => ['nullable', Rule::exists('penyedia', 'id')->where('opd_id', $opdId)],
            'items' => ['required', 'array', 'min:1'],
            'items.*.anggaran_rekening_id' => $gaji
                ? ['required', 'distinct', Rule::exists('anggaran_rekening', 'id')->where('opd_id', $opdId)]
                : ['nullable'],
            'items.*.uraian' => ['required', 'string', 'max:255'],
            'items.*.volume' => ['required', 'string', 'max:20'],
            'items.*.satuan' => ['required', 'string', 'max:30'],
            'items.*.harga_satuan' => ['required', 'string', 'max:30'],
            'items.*.jabatan' => ['nullable', 'string', 'max:255'],
            'items.*.nama_bank' => ['nullable', 'string', 'max:100'],
            'items.*.no_rekening' => ['nullable', 'string', 'max:50'],
            'items.*.pajak' => ['nullable', 'string', 'max:30'],
            'kategori' => ['required', Rule::in(array_keys(Spj::KATEGORI))],
        ];
        $atribut = [
            'jenis' => 'Jenis', 'tanggal' => 'Tanggal', 'anggaran_rekening_id' => 'Rekening belanja', 'sumber_dana_id' => 'Sumber dana',
            'uraian' => 'Uraian pekerjaan', 'penyedia_id' => 'Penyedia', 'items' => 'Item belanja',
            'items.*.uraian' => 'Uraian item', 'items.*.volume' => 'Volume', 'items.*.satuan' => 'Satuan', 'items.*.harga_satuan' => 'Harga satuan',
            'kategori' => 'Jenis belanja',
        ];
        foreach (Spj::PEJABAT as $kolom => [$label, $jabatan]) {
            $aturan[$kolom] = ['nullable', Rule::exists('pejabat_opd', 'id')->where('opd_id', $opdId)->whereIn('jabatan', $jabatan)];
            $atribut[$kolom] = $label;
        }

        $pesanItem = $gaji ? 'Isi nilai gaji minimal satu rekening.' : 'Isi minimal satu item belanja.';
        $data = $request->validate($aturan, PesanValidasi::UMUM + [
            'items.min' => $pesanItem, 'items.required' => $pesanItem,
        ], $atribut);

        $galat = [];
        foreach ($data['items'] as $i => $item) {
            $volume = Uang::volumeSeratus($item['volume']);
            $harga = Uang::dariInput($item['harga_satuan']);
            if ($volume === null || $volume <= 0) {
                $galat["items.$i.volume"] = 'Volume harus angka lebih dari 0 (maksimal 2 desimal).';
            }
            if ($harga === null || $harga <= 0) {
                $galat["items.$i.harga_satuan"] = 'Nilai harus angka lebih dari 0.';
            }
        }

        $rekIds = $gaji ? array_column($data['items'], 'anggaran_rekening_id') : [$data['anggaran_rekening_id']];
        $daftar = AnggaranRekening::with('subKegiatan:id,kode')->whereIn('id', $rekIds)->get();
        $kunci = $gaji ? 'items' : 'anggaran_rekening_id';
        $versiIds = $daftar->pluck('dpa_versi_id')->unique();
        $versi = $versiIds->count() === 1 ? DpaVersi::withoutGlobalScopes()->find($versiIds->first()) : null;

        if (! $versi || ! $versi->aktif) {
            $galat[$kunci] = 'Rekening ini bukan bagian dari DPA aktif.';
        } else {
            $batas = AksesSubKegiatan::batasan($me, $versi->tahun);
            foreach ($daftar as $ar) {
                if ($batas !== null && ! in_array($ar->subKegiatan->kode, $batas, true)) {
                    $galat[$kunci] = 'Rekening ini bukan dari sub kegiatan yang ditugaskan kepada Anda.';
                    break;
                }
            }
            if ($gaji && $daftar->pluck('sub_kegiatan_id')->unique()->count() > 1) {
                $galat['items'] = 'Rekening gaji harus berasal dari satu sub kegiatan.';
            }
            if (Carbon::parse($data['tanggal'])->year !== (int) $versi->tahun) {
                $galat['tanggal'] = "Tanggal harus berada di tahun anggaran {$versi->tahun}.";
            }
            if ($spj && (int) $spj->tahun !== (int) $versi->tahun) {
                $galat[$kunci] = 'Rekening harus dari tahun anggaran yang sama dengan SPJ ini.';
            }
        }
        if ($galat) {
            throw ValidationException::withMessages($galat);
        }

        if ($gaji) {
            $data['anggaran_rekening_id'] = collect($data['items'])->first()['anggaran_rekening_id']; // jangkar
        }

        $items = $data['items'];
        unset($data['items']);

        return [$data, $items];
    }

    public function cetak(Request $request, Spj $spj): View
    {
        $this->pastikanBolehLihat($request->user(), $spj);
        $spj->load(['items', 'sumberDana', 'penyedia', 'pembuat', 'anggaranRekening.rekening', 'anggaranRekening.subKegiatan']);

        $ids = array_filter($spj->only(array_keys(Spj::PEJABAT)));
        $pejabat = \App\Models\PejabatOpd::withoutGlobalScopes()->whereIn('id', $ids)->get()->keyBy('id');
        $opd = \App\Models\Opd::find($spj->opd_id);

        return view('spj.cetak', compact('spj', 'pejabat', 'opd'));
    }
}