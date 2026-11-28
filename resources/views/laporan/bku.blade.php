@extends('layouts.app')

@section('title', 'Buku kas umum')
@section('nav', 'bku')

@push('styles')
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endpush

@section('content')
    @php
        $parameter = array_merge($kueri, ['dari' => $dari->toDateString(), 'sampai' => $sampai->toDateString(), 'tahun' => $tahun, 'opd' => $opd?->id]);
    @endphp

    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1" style="color:var(--hutan)">Buku kas umum</h1>
            <p class="text-secondary mb-0" style="max-width:75ch">
                Pengeluaran dari SPJ berstatus final menurut tanggal SPJ. Penerimaan kas (misalnya SP2D UP atau GU) belum dicatat di aplikasi ini, jadi saldo kas tidak ditampilkan.
            </p>
        </div>
        @if ($opd)
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('laporan.bku.excel', $parameter) }}" class="btn btn-outline-daun"><i class="bi bi-file-earmark-excel me-1"></i> Excel</a>
                <a href="{{ route('laporan.bku.pdf', $parameter) }}" target="_blank" rel="noopener" class="btn btn-kuning"><i class="bi bi-file-earmark-pdf me-1"></i> PDF</a>
            </div>
        @endif
    </div>

    @include('laporan._filter', ['aksi' => 'laporan.bku', 'mode' => 'rentang'])

    @if (! $opd)
        <section class="panel p-4 text-secondary">
            @if ($daftarOpd->isNotEmpty())
                Pilih OPD di atas untuk melihat laporannya.
            @else
                Akun Anda belum terhubung ke OPD. Hubungi admin untuk mengaturnya.
            @endif
        </section>
    @else
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="panel p-3 h-100"><div class="small text-secondary">Jumlah SPJ final</div><div class="fs-4 fw-bold angka">{{ $ringkasan['jumlah'] }}</div></div>
            </div>
            <div class="col-6 col-md-5">
                <div class="panel p-3 h-100"><div class="small text-secondary">Total pengeluaran periode ini</div><div class="fs-4 fw-bold angka">{{ \App\Support\Uang::rupiah($ringkasan['total_sen']) }}</div></div>
            </div>
        </div>

        <section class="panel p-3">
            <table id="tabel" class="table tabel w-100">
                <thead>
                <tr><th>Tanggal</th><th>No. SPJ</th><th>Uraian</th><th>Rekening</th><th>Sumber dana</th><th class="text-end">Jumlah</th><th></th></tr>
                </thead>
            </table>
        </section>
    @endif
@endsection

@if ($opd)
    @push('scripts')
        @include('partials.datatables-aset')
        <script>
            $('#tabel').DataTable({
                processing: true, serverSide: true, language: DT_BAHASA,
                ajax: @json(route('laporan.bku.data', $parameter)),
                order: [[0, 'asc'], [1, 'asc']],
                columns: [
                    { data: 'tanggal', searchable: false },
                    { data: 'nomor_urut', className: 'angka' },
                    { data: 'uraian' },
                    { data: 'rekening', className: 'angka', orderable: false, searchable: false },
                    { data: 'sumber_dana', orderable: false, searchable: false },
                    { data: 'total', className: 'text-end angka', searchable: false },
                    { data: 'aksi', orderable: false, searchable: false, className: 'text-end' }
                ]
            });
        </script>
    @endpush
@endif