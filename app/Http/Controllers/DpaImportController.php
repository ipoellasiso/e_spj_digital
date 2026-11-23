<?php

namespace App\Http\Controllers;

use App\Exceptions\DpaImportException;
use App\Http\Controllers\Concerns\KonteksOpd;
use App\Models\DpaVersi;
use App\Models\ImportDpaBatch;
use App\Models\ImportDpaRow;
use App\Models\Opd;
use App\Services\DpaImportService;
use App\Support\PesanValidasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

/** Impor DPA dari Excel: superadmin, admin OPD, dan bendahara (dijaga di route). */
class DpaImportController extends Controller
{
    use KonteksOpd;

    public function __construct(private DpaImportService $service)
    {
    }

    public function form(Request $request): View
    {
        $me = $request->user();

        return view('dpa.impor', [
            'riwayat' => ImportDpaBatch::with('opd:id,nama')->latest('id')->limit(10)->get(),
            'daftarOpd' => $me->isSuperadmin() ? Opd::orderBy('nama')->get(['id', 'nama']) : collect(),
            'opdSendiri' => $me->isSuperadmin() ? null : $me->opd,
        ]);
    }

    public function stage(Request $request): RedirectResponse
    {
        $me = $request->user();

        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
            'tahun' => ['required', 'integer', 'between:2000,2100'],
            'jenis' => ['required', Rule::in(['murni', 'perubahan'])],
            'nomor_dpa' => ['nullable', 'string', 'max:255'],
            'opd_id' => [Rule::requiredIf($me->isSuperadmin()), 'nullable', 'exists:opd,id'],
        ], PesanValidasi::UMUM + [
            'file.required' => 'Pilih file Excel DPA.',
            'file.max' => 'Ukuran file maksimal 5 MB.',
            'file.mimes' => 'File harus berformat .xlsx atau .xls.',
            'between' => ':attribute tidak valid.',
        ], [
            'file' => 'File', 'tahun' => 'Tahun', 'jenis' => 'Jenis DPA', 'nomor_dpa' => 'Nomor DPA', 'opd_id' => 'OPD',
        ]);

        $opd = $me->isSuperadmin() ? Opd::findOrFail($data['opd_id']) : $me->opd;
        abort_unless($opd, 403, 'Akun Anda belum terhubung ke OPD.');

        $berkas = $request->file('file');
        try {
            $batch = $this->service->stage($opd, $berkas, $berkas->getClientOriginalName(), [
                'tahun' => (int) $data['tahun'], 'jenis' => $data['jenis'], 'nomor_dpa' => $data['nomor_dpa'] ?? null,
            ], $me->id);
        } catch (Throwable $e) {
            Log::warning('Impor DPA gagal dibaca: ' . $e->getMessage());

            return back()->withInput()->withErrors(['file' => 'File tidak bisa dibaca. Pastikan berupa file Excel yang valid.']);
        }

        return redirect()->route('dpa.impor.show', $batch);
    }

    public function show(ImportDpaBatch $batch): View
    {
        $aktif = DpaVersi::withoutGlobalScopes()
            ->where('opd_id', $batch->opd_id)->where('tahun', $batch->tahun)->where('aktif', true)->first();

        return view('dpa.impor-detail', ['batch' => $batch->load('opd:id,nama'), 'aktif' => $aktif]);
    }

    /** Baris staging, server-side. ?galat=1 hanya menampilkan baris bermasalah. */
    public function baris(Request $request, ImportDpaBatch $batch): JsonResponse
    {
        $query = ImportDpaRow::query()->where('batch_id', $batch->id)
            ->when($request->boolean('galat'), fn ($q) => $q->whereNotNull('pesan_error'));

        return DataTables::eloquent($query)->toJson();
    }

    public function komit(ImportDpaBatch $batch): RedirectResponse
    {
        try {
            $versi = $this->service->commit($batch);
        } catch (DpaImportException $e) {
            return back()->withErrors(['komit' => $e->getMessage()]);
        }

        return redirect()
            ->route('dpa.index', ['opd' => $batch->opd_id, 'tahun' => $batch->tahun])
            ->with('sukses', "DPA {$versi->jenis} versi {$versi->nomor_versi} tahun {$batch->tahun} sekarang aktif.");
    }

    public function buang(ImportDpaBatch $batch): RedirectResponse
    {
        abort_if($batch->status === 'dikomit', 403, 'Hasil unggah yang sudah dikonfirmasi tidak bisa dibuang.');
        $batch->delete();

        return redirect()->route('dpa.impor')->with('sukses', 'Hasil unggah dibuang.');
    }
}