@extends('layouts.app')

@section('title', 'Verifikasi Tagihan')
@section('nav', 'superadmin-tagihan')

@section('content')
<div class="container py-4">
    <h1 class="page-title mb-4">Daftar Tagihan Masuk</h1>

    @if(session('sukses'))
        <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i>{{ session('sukses') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}</div>
    @endif

    <div class="card-tabel p-4">
        <div class="table-responsive">
            <table class="tabel tabel-modern w-100">
                <thead>
                    <tr>
                        <th>No Invoice</th>
                        <th>OPD</th>
                        <th>Paket</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Bukti Transfer</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tagihans as $t)
                    <tr>
                        <td class="fw-bold">{{ $t->no_invoice }}</td>
                        <td>{{ $t->opd->nama }}</td>
                        <td>{{ $t->paket->nama }}</td>
                        <td>Rp{{ number_format($t->total_bayar, 0, ',', '.') }}</td>
                        <td>
                            @if($t->status == 'paid')
                                <span class="badge bg-success">Lunas</span>
                            @elseif($t->status == 'unpaid')
                                <span class="badge bg-warning text-dark">Menunggu Pembayaran</span>
                            @else
                                <span class="badge bg-secondary">Expired</span>
                            @endif
                        </td>
                        <td>
                            @if($t->bukti_transfer)
                                <a href="{{ asset('storage/' . $t->bukti_transfer) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-image"></i> Lihat Bukti
                                </a>
                            @else
                                <span class="text-muted small">Belum ada</span>
                            @endif
                        </td>
                        <td>
                            @if($t->status == 'unpaid' && $t->bukti_transfer)
                                {{-- Form untuk memanggil fungsi verifikasiPembayaran --}}
                                <form action="{{ route('superadmin.tagihan.verifikasi', $t->id) }}" method="POST" onsubmit="return confirm('Verifikasi pembayaran ini dan perpanjang masa aktif OPD?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success fw-bold">
                                        <i class="bi bi-check2-circle me-1"></i> Verifikasi & Aktifkan
                                    </button>
                                </form>
                            @elseif($t->status == 'paid')
                                <span class="text-success small fw-bold"><i class="bi bi-check-all"></i> Selesai</span>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada tagihan yang dibuat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection