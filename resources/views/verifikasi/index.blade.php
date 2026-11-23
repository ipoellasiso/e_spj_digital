@extends('layouts.app')

@section('title', $judul)
@section('nav', 'verifikasi')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $judul }}</h1>
            <p class="page-desc">Daftar SPJ yang menunggu tindakan Anda.</p>
        </div>
    </div>

    <div class="card-tabel">
        <div class="table-responsive">
            <table class="tabel tabel-modern w-100">
                <thead>
                    <tr>
                        <th>No. SPJ</th>
                        <th>Pembuat</th>
                        <th>Uraian</th>
                        <th class="text-end">Jumlah</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftar_spj as $s)
                        <tr>
                            <td class="fw-bold">{{ $s->nomor ?? '-' }}</td>
                            <td>{{ $s->pembuat?->name }}</td>
                            <td>{{ Str::limit($s->uraian, 40) }}</td>
                            <td class="text-end angka">Rp{{ number_format($s->total / 100, 2, ',', '.') }}</td>
                            <td><span class="badge {{ $s->status->badge() }}">{{ $s->status->label() }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('spj.show', $s) }}" class="btn-aksi-ikon text-primary" title="Buka Detail"><i class="bi bi-box-arrow-in-right"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada antrian saat ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection