@extends('layouts.app')
@section('title', 'Langganan & Tagihan')
@section('nav', 'billing')

@section('content')
<div class="container py-4">
    <h1 class="page-title mb-4">Langganan & Tagihan</h1>

    @if(session('sukses'))
        <div class="alert alert-success">{{ session('sukses') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- BANNER STATUS LANGGANAN --}}
    @if($isExpired)
        {{-- Tampilan Jika Expired / Belum Langganan --}}
        <div class="card-modern p-4 mb-4" style="background: linear-gradient(135deg, #7f1d1d, #450a0a); color: white; border-left: 5px solid #ef4444;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> Langganan Berakhir!</h4>
                    <p class="mb-0 opacity-75">
                        Masa aktif langganan OPD <strong>{{ $opd->nama }}</strong> telah berakhir 
                        @if($opd->tanggal_berakhir) 
                            pada {{ \Carbon\Carbon::parse($opd->tanggal_berakhir)->translatedFormat('d F Y') }}
                        @endif.
                        Akses ke sistem terbatas. Mohon segera lakukan perpanjangan.
                    </p>
                </div>
                <i class="bi bi-shield-x fs-1 opacity-50 d-none d-md-block"></i>
            </div>
        </div>
    @else
        {{-- Tampilan Jika Aktif --}}
        <div class="card-modern p-4 mb-4" style="background: linear-gradient(135deg, #064e3b, #022c22); color: white;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-1">Status Langganan: {{ $opd->nama }}</h4>
                    <p class="mb-0 opacity-75">
                        Aktif hingga: {{ \Carbon\Carbon::parse($opd->tanggal_berakhir)->translatedFormat('d F Y') }}
                        @if($sisaHari !== null && $sisaHari <= 7)
                            <span class="badge bg-warning text-dark ms-2">Sisa {{ $sisaHari }} Hari Lagi!</span>
                        @endif
                    </p>
                </div>
                <i class="bi bi-shield-check fs-1 opacity-50 d-none d-md-block"></i>
            </div>
        </div>
    @endif

    {{-- AREA TAGIHAN --}}
    @if($tagihanAktif)
        <div class="card-tabel p-4 mb-4 border border-warning">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="badge bg-warning text-dark mb-2">MENUNGGU PEMBAYARAN</span>
                    <h4 class="fw-bold">Invoice: {{ $tagihanAktif->no_invoice }}</h4>
                    <p class="text-secondary mb-1">Paket: <strong>{{ $tagihanAktif->paket->nama }}</strong></p>
                    <p class="text-secondary">Periode: {{ $tagihanAktif->periode_mulai->format('d/m/Y') }} s/d {{ $tagihanAktif->periode_berakhir->format('d/m/Y') }}</p>
                    <h3 class="fw-bold text-danger mt-3">Total: Rp{{ number_format($tagihanAktif->total_bayar, 0, ',', '.') }}</h3>
                </div>
                
                <div class="text-end">
                    @if($tagihanAktif->bukti_transfer)
                        <span class="badge bg-info mb-2">Bukti Transfer Terkirim</span>
                        <p class="small text-secondary mb-0">Menunggu verifikasi Superadmin.</p>
                    @else
                        <p class="small text-secondary mb-2">Silakan transfer ke:<br><strong>Bank BNI 1234567890</strong><br>a/n e-SPJ Digital</p>
                        <form action="{{ route('billing.upload', $tagihanAktif->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="bukti" class="form-control form-control-sm mb-2" required>
                            <button type="submit" class="btn btn-warning fw-bold w-100">Unggah Bukti</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @elseif($isExpired)
        {{-- FORM PERPANJANGAN MANDIRI JIKA EXPIRED & TIDAK ADA TAGIHAN AKTIF --}}
        <div class="card-tabel p-4 mb-4 border border-danger" style="background: #fef2f2;">
            <h5 class="fw-bold text-danger mb-3">Buat Tagihan Perpanjangan</h5>
            <p class="text-secondary small mb-3">Pilih paket langganan yang ingin Anda perpanjang. Sistem akan menerbitkan invoice baru untuk Anda bayar.</p>
            <form action="{{ route('billing.perpanjang') }}" method="POST" class="d-flex gap-2 align-items-end">
                @csrf
                <div style="flex: 1;">
                    <label class="form-label small fw-bold">Pilih Paket</label>
                    <select name="paket_id" class="form-select" required>
                        @foreach($daftarPaket as $p)
                            <option value="{{ $p->id }}">{{ $p->nama }} - Rp{{ number_format($p->harga_bulanan, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-danger fw-bold px-4 py-2">Buat Tagihan</button>
            </form>
        </div>
    @endif

    {{-- RIWAYAT TAGIHAN --}}
    <h4 class="fw-bold mb-3 mt-5">Riwayat Transaksi</h4>
    <div class="card-tabel">
        <table class="tabel tabel-modern w-100">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Paket</th>
                    <th>Periode</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($riwayat as $r)
                <tr>
                    <td class="fw-bold">{{ $r->no_invoice }}</td>
                    <td>{{ $r->paket->nama }}</td>
                    <td>{{ $r->periode_mulai->format('d/m/Y') }} - {{ $r->periode_berakhir->format('d/m/Y') }}</td>
                    <td>Rp{{ number_format($r->total_bayar, 0, ',', '.') }}</td>
                    <td>
                        @if($r->status == 'paid')
                            <span class="badge bg-success">Lunas</span>
                        @else
                            <span class="badge bg-secondary">Expired</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted">Belum ada riwayat transaksi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection