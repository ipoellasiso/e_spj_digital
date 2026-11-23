@extends('layouts.app')

@section('title', $judul)
@section('nav', $nav)

@push('styles')
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endpush

{{-- Halaman daftar generik. Variabel: judul, nav, deskripsi, urlData, kolom[], urlBaru?, labelBaru?, urutAwal?, urutArah? --}}
@section('content')
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1" style="color:var(--hutan)">{{ $judul }}</h1>
            @isset($deskripsi)<p class="text-secondary mb-0" style="max-width:70ch">{{ $deskripsi }}</p>@endisset
        </div>
        @isset($urlBaru)
            <a href="{{ $urlBaru }}" class="btn btn-kuning"><i class="bi bi-plus-lg me-1"></i> {{ $labelBaru ?? 'Tambah' }}</a>
        @endisset
    </div>

    <section class="panel p-3">
        <table id="tabel" class="table tabel w-100">
            <thead>
            <tr>
                @foreach ($kolom as $k)<th class="{{ str_contains($k['kelas'] ?? '', 'text-end') ? 'text-end' : '' }}">{{ $k['judul'] }}</th>@endforeach
                <th></th>
            </tr>
            </thead>
        </table>
    </section>
@endsection

@push('scripts')
    @include('partials.datatables-aset')
    <script>
        var kolom = @json($kolom).map(function (k) {
            return { data: k.data, className: k.kelas || '', orderable: k.urut !== false, searchable: k.cari !== false, defaultContent: '-' };
        });
        kolom.push({ data: 'aksi', orderable: false, searchable: false, className: 'text-end', defaultContent: '' });

        $('#tabel').DataTable({
            processing: true, serverSide: true, language: DT_BAHASA,
            ajax: @json($urlData),
            order: [[{{ $urutAwal ?? 0 }}, @json($urutArah ?? 'asc')]],
            columns: kolom
        });
    </script>
@endpush