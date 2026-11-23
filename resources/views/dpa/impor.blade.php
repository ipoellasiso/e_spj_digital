@extends('layouts.app')

@section('title', 'Impor DPA')
@section('nav', 'impor-dpa')

@push('styles')
<style>
    /* Styling Khusus Upload File */
    .upload-area {
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        padding: 40px 20px;
        text-align: center;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }
    .upload-area:hover, .upload-area.dragover {
        background: rgba(16, 185, 129, 0.05);
        border-color: var(--hijau-500);
    }
    .upload-area input[type="file"] {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        opacity: 0; cursor: pointer;
    }
    .upload-icon {
        font-size: 48px;
        color: var(--hijau-500);
        margin-bottom: 16px;
        transition: transform 0.3s ease;
    }
    .upload-area:hover .upload-icon {
        transform: translateY(-5px) scale(1.1);
    }
</style>
@endpush

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Impor DPA dari Excel</h1>
            <p class="page-desc">File dibaca dulu dan diperiksa. Data baru masuk ke DPA setelah Anda mengonfirmasinya.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-5">
            <form method="POST" action="{{ route('dpa.impor.stage') }}" enctype="multipart/form-data" novalidate class="form-card">
                @csrf
                @if ($opdSendiri)
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">OPD</label>
                        <input class="form-control bg-light" value="{{ $opdSendiri->nama }}" disabled>
                    </div>
                @else
                    <div class="mb-3">
                        <label for="opd_id" class="form-label fw-bold text-secondary">OPD</label>
                        <select id="opd_id" name="opd_id" class="form-select @error('opd_id') is-invalid @enderror">
                            <option value="">Pilih OPD</option>
                            @foreach ($daftarOpd as $o)
                                <option value="{{ $o->id }}" @selected((string) old('opd_id') === (string) $o->id)>{{ $o->nama }}</option>
                            @endforeach
                        </select>
                        @error('opd_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                @endif

                <div class="row g-3 mb-3">
                    <div class="col-5">
                        <label for="tahun" class="form-label fw-bold text-secondary">Tahun</label>
                        <input id="tahun" name="tahun" type="number" min="2000" max="2100" value="{{ old('tahun', now()->year) }}"
                               class="form-control @error('tahun') is-invalid @enderror">
                        @error('tahun')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-7">
                        <label for="jenis" class="form-label fw-bold text-secondary">Jenis DPA</label>
                        <select id="jenis" name="jenis" class="form-select @error('jenis') is-invalid @enderror">
                            <option value="murni" @selected(old('jenis', 'murni') === 'murni')>Murni</option>
                            <option value="perubahan" @selected(old('jenis') === 'perubahan')>Perubahan</option>
                        </select>
                        @error('jenis')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="nomor_dpa" class="form-label fw-bold text-secondary">Nomor DPA <span class="fw-normal">(opsional)</span></label>
                    <input id="nomor_dpa" name="nomor_dpa" value="{{ old('nomor_dpa') }}" placeholder="DPA/7.01.01.2.09.0002/001/2026"
                           class="form-control @error('nomor_dpa') is-invalid @enderror">
                    @error('nomor_dpa')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- DRAG & DROP UPLOAD AREA --}}
                <div class="mb-4">
                    <label class="form-label fw-bold text-secondary">File Excel</label>
                    <div class="upload-area" id="drop-area">
                        <input id="file" name="file" type="file" accept=".xlsx,.xls" class="@error('file') is-invalid @enderror" onchange="updateFileName(this)">
                        <i class="bi bi-cloud-arrow-up upload-icon"></i>
                        <h5 class="fw-bold mb-1" id="file-name">Pilih file atau seret ke sini</h5>
                        <p class="text-muted small mb-0">Hanya format .xlsx atau .xls. Maksimal 5 MB.</p>
                    </div>
                    @error('file')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn-premium w-100 py-3 justify-content-center fs-6">
                    <i class="bi bi-search me-2"></i> Unggah & Periksa File
                </button>
            </form>
        </div>

        <div class="col-12 col-xl-7">
            <div class="card-tabel h-100">
                <div class="card-header bg-white border-bottom-0 pb-0">
                    <h5 class="fw-bold mb-0">Unggahan Terakhir</h5>
                </div>
                <div class="table-responsive p-3">
                    <table class="tabel tabel-modern w-100">
                        <thead>
                            <tr><th>Waktu</th><th>OPD</th><th>Tahun</th><th>Jenis</th><th>Status</th><th class="text-end">Aksi</th></tr>
                        </thead>
                        <tbody>
                        @forelse ($riwayat as $b)
                            <tr>
                                <td class="angka fw-semibold">{{ $b->created_at->format('d/m/y H:i') }}</td>
                                <td>{{ $b->opd?->nama }}</td>
                                <td>{{ $b->tahun }}</td>
                                <td>{{ ucfirst($b->jenis) }}</td>
                                <td>
                                    @if ($b->status === 'siap') <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-3">Siap</span>
                                    @elseif ($b->status === 'dikomit') <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3">Dipakai</span>
                                    @else <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill px-3">Gagal</span> @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('dpa.impor.show', $b) }}" class="btn-aksi-ikon text-primary" title="Buka Detail"><i class="bi bi-box-arrow-in-right"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                Belum ada riwayat unggahan.
                            </td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Script untuk menampilkan nama file saat dipilih
    function updateFileName(input) {
        var fileName = input.files[0] ? input.files[0].name : "Pilih file atau seret ke sini";
        document.getElementById('file-name').innerText = fileName;
        document.getElementById('file-name').classList.add('text-success');
    }

    // Script untuk efek Drag & Drop
    var dropArea = document.getElementById('drop-area');
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });
    function preventDefaults (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, () => dropArea.classList.add('dragover'), false);
    });
    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, () => dropArea.classList.remove('dragover'), false);
    });
</script>
@endpush