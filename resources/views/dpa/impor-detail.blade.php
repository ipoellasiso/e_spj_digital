@extends('layouts.app')

@section('title', 'Hasil unggah DPA')
@section('nav', 'impor-dpa')

@push('styles')
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endpush

@section('content')
    @php
        $rp = fn ($v) => 'Rp' . number_format((float) $v, 2, ',', '.');
    @endphp

    <div class="page-header mb-4">
        <div>
            <a href="{{ route('dpa.impor') }}" class="back-link mb-2">
                <i class="bi bi-arrow-left"></i> Kembali ke Impor DPA
            </a>
            <h1 class="page-title">Hasil Unggah DPA</h1>
            <p class="page-desc text-primary fw-semibold"><i class="bi bi-file-earmark-excel me-1"></i> {{ $batch->nama_file }}</p>
        </div>
    </div>

    @error('komit')
        <div class="alert alert-danger border-0 shadow-sm rounded-4" role="alert" style="white-space:pre-line">{{ $message }}</div>
    @enderror

    {{-- Kartu Ringkasan --}}
    <div class="card-modern p-4 mb-4">
        <div class="row g-4 mb-4">
            <div class="col-6 col-md-3">
                <div class="text-muted small fw-bold text-uppercase mb-1">OPD</div>
                <div class="fw-bold text-dark">{{ $batch->opd?->nama }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-muted small fw-bold text-uppercase mb-1">Tahun / Jenis</div>
                <div class="fw-bold text-dark">{{ $batch->tahun }}, {{ ucfirst($batch->jenis) }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-muted small fw-bold text-uppercase mb-1">Nomor DPA</div>
                <div class="fw-bold text-dark">{{ $batch->nomor_dpa ?: '-' }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-muted small fw-bold text-uppercase mb-1">Unit di File</div>
                <div class="fw-bold text-dark angka">{{ $batch->unit_kode ?: '-' }}</div>
            </div>
        </div>
        
        <hr class="border-light my-4">

        <div class="row g-3 text-center">
            <div class="col-6 col-md">
                <div class="p-3 bg-light rounded-3">
                    <div class="text-secondary small mb-1">Program</div>
                    <div class="fs-4 fw-bold angka text-dark">{{ $batch->jumlah_program }}</div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="p-3 bg-light rounded-3">
                    <div class="text-secondary small mb-1">Kegiatan</div>
                    <div class="fs-4 fw-bold angka text-dark">{{ $batch->jumlah_kegiatan }}</div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="p-3 bg-light rounded-3">
                    <div class="text-secondary small mb-1">Sub Kegiatan</div>
                    <div class="fs-4 fw-bold angka text-dark">{{ $batch->jumlah_sub_kegiatan }}</div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="p-3 bg-light rounded-3">
                    <div class="text-secondary small mb-1">Rekening</div>
                    <div class="fs-4 fw-bold angka text-dark">{{ $batch->jumlah_rekening }}</div>
                </div>
            </div>
            <div class="col-12 col-md-4 mt-md-0 mt-3">
                <div class="p-3 bg-success bg-opacity-10 border border-success-subtle rounded-3">
                    <div class="text-success small fw-bold mb-1">Total Pagu</div>
                    <div class="fs-4 fw-bold angka text-success">{{ $rp($batch->total_pagu) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Status & Aksi --}}
    @if ($batch->status === 'gagal')
        <div class="alert alert-danger border-0 shadow-sm rounded-4 p-4 mb-4">
            <h5 class="fw-bold"><i class="bi bi-x-circle-fill me-2"></i> File belum bisa dipakai.</h5>
            <p class="mb-2">{{ $batch->jumlah_error }} masalah ditemukan. Perbaiki file sumbernya, lalu unggah ulang.</p>
            @if ($batch->catatan)
                <ul class="mb-0 bg-white p-3 rounded-3">
                    @foreach ($batch->catatan as $c)<li>{{ $c }}</li>@endforeach
                </ul>
            @endif
        </div>
        <div class="d-flex gap-2 mb-4">
            <form method="POST" action="{{ route('dpa.impor.buang', $batch) }}">
                @csrf @method('DELETE')
                <button type="submit" class="btn-batal">Buang hasil ini</button>
            </form>
            <a href="{{ route('dpa.impor') }}" class="btn-premium">Unggah ulang file baru</a>
        </div>
    @elseif ($batch->status === 'siap')
        <div class="alert alert-success border-0 shadow-sm rounded-4 p-4 mb-4">
            <h5 class="fw-bold"><i class="bi bi-check-circle-fill me-2"></i> File lolos pemeriksaan!</h5>
            <p class="mb-0">Tidak ada masalah struktural yang ditemukan pada file Excel ini.</p>
        </div>
        @if ($aktif)
            <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Ini akan menggantikan <strong>DPA {{ $aktif->jenis }} versi {{ $aktif->nomor_versi }}</strong> yang sedang aktif.
                Realisasi dan SPJ yang sedang berproses dibawa ke versi baru.
            </div>
        @endif
        <div class="mb-4 d-flex flex-wrap gap-3">
            {{-- <form method="POST" action="{{ route('dpa.impor.komit', $batch) }}" onsubmit="return confirm('Jadikan file ini sebagai DPA aktif?')"> --}}
            <form method="POST" action="{{ route('dpa.impor.komit', $batch) }}" class="form-konfirmasi">
                @csrf
                <button type="submit" class="btn-premium py-3 px-4 fs-6">
                    <i class="bi bi-check2-all me-1"></i> Konfirmasi & Jadikan DPA Aktif
                </button>
            </form>
            <form method="POST" action="{{ route('dpa.impor.buang', $batch) }}">
                @csrf @method('DELETE')
                <button type="submit" class="btn-batal py-3 px-4">Buang hasil ini</button>
            </form>
        </div>
    @else
        <div class="alert alert-info border-0 shadow-sm rounded-4 p-4 mb-4">
            <i class="bi bi-info-circle-fill me-2"></i> Hasil unggah ini sudah menjadi DPA aktif. 
            <a href="{{ route('dpa.index', ['opd' => $batch->opd_id, 'tahun' => $batch->tahun]) }}" class="fw-bold text-decoration-none ms-2">Lihat DPA &rarr;</a>
        </div>
    @endif

    {{-- Tabel Rincian File --}}
    <div class="card-tabel">
        <div class="p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-1 pb-3">
                <h5 class="fw-bold mb-0">Isi File Excel</h5>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="hanya-galat" @checked($batch->status === 'gagal')>
                    <label class="form-check-label fw-semibold text-danger" for="hanya-galat">Hanya tampilkan baris bermasalah</label>
                </div>
            </div>
            <table id="tabel" class="tabel tabel-modern w-100">
                <thead><tr><th>Baris</th><th>Tingkat</th><th>Kode</th><th>Uraian</th><th class="text-end">Pagu</th><th>Masalah</th></tr></thead>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    @include('partials.datatables-aset')
    <script>
        var URL_BARIS = @json(route('dpa.impor.baris', $batch));
        var TINGKAT = { program: 'Program', kegiatan: 'Kegiatan', sub_kegiatan: 'Sub Kegiatan', rekening: 'Rekening', tidak_dikenal: 'Tidak Dikenal' };
        var cek = document.getElementById('hanya-galat');

        var tabel = $('#tabel').DataTable({
            processing: true, serverSide: true, language: DT_BAHASA,
            ajax: URL_BARIS + '?galat=' + (cek.checked ? 1 : 0),
            order: [[0, 'asc']],
            dom: '<"tabel-toolbar d-flex justify-content-between align-items-center"lf>t<"tabel-footer d-flex justify-content-between align-items-center"ip>',
            classes: { sFilterInput: "form-control ms-2", sLengthSelect: "form-select" },
            columns: [
                { data: 'baris', className: 'angka fw-semibold' },
                { data: 'level', render: function (v) { return '<span class="badge bg-secondary">'+(TINGKAT[v] || esc(v))+'</span>'; } },
                { data: 'kode', className: 'angka text-primary' },
                { data: 'uraian' },
                { data: 'pagu', className: 'text-end angka', searchable: false, render: rupiah },
                { data: 'pesan_error', orderable: false, searchable: false, defaultContent: '',
                  render: function (v) { return v ? '<span class="text-danger fw-medium"><i class="bi bi-exclamation-circle me-1"></i>'+esc(v)+'</span>' : '<span class="text-success"><i class="bi bi-check-circle"></i> Aman</span>'; } }
            ]
        });
        
        cek.addEventListener('change', function () {
            tabel.ajax.url(URL_BARIS + '?galat=' + (cek.checked ? 1 : 0)).load();
        });

                // --- SCRIPT KONFIRMASI JADIKAN DPA AKTIF ---
        $(document).on('submit', '.form-konfirmasi', function(e) {
            e.preventDefault(); 
            
            var form = this;
            
            Swal.fire({
                title: 'Jadikan DPA Aktif?',
                text: "Tindakan ini akan menggantikan DPA yang sedang aktif dengan data dari file Excel ini.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669', // Hijau
                cancelButtonColor: '#64748b',  // Abu-abu
                confirmButtonText: '<i class="bi bi-check2-all me-1"></i> Ya, Jadikan Aktif',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'rounded-4 border-0 shadow-lg',
                    title: 'fw-bold text-dark',
                    htmlContainer: 'text-secondary',
                    confirmButton: 'btn-premium',
                    cancelButton: 'btn-batal ms-2'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    </script>
@endpush