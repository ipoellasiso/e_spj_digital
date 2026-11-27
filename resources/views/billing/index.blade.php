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
            <p class="text-secondary small mb-3">Pilih paket dan durasi langganan yang ingin Anda perpanjang. Sistem akan menerbitkan invoice baru.</p>
            
            <form action="{{ route('billing.perpanjang') }}" method="POST">
                @csrf
                                <div class="row g-3 align-items-end">
                    
                    {{-- Pilih Paket --}}
                    <div class="col-md-5">
                        <label class="form-label small fw-bold">Pilih Paket</label>
                        <select name="paket_id" id="pilih_paket_billing" class="form-select" required>
                            <option value="" data-harga-bulanan="0" data-harga-tahunan="0" data-durasi="">-- Pilih Paket --</option>
                            @foreach($daftarPaket as $p)
                                <option value="{{ $p->id }}" 
                                        data-harga-bulanan="{{ $p->harga_bulanan }}" 
                                        data-harga-tahunan="{{ $p->harga_tahunan }}"
                                        {{-- Tentukan durasi otomatis di sini. Jika ada harga tahunan, set ke tahunan. Jika tidak, bulanan --}}
                                        data-durasi="{{ $p->harga_tahunan ? 'tahunan' : 'bulanan' }}">
                                    {{ $p->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Durasi (Readonly) --}}
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Durasi Tagihan (Otomatis)</label>
                        <select name="durasi" id="pilih_durasi_billing" class="form-select bg-light" style="pointer-events: none;" readonly required>
                            <option value="bulanan">Bulanan (1 Bulan)</option>
                            <option value="tahunan">Tahunan (12 Bulan)</option>
                        </select>
                    </div>

                    {{-- Tombol --}}
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-danger fw-bold w-100 py-2">Buat Tagihan</button>
                    </div>
                </div>
                
                {{-- Info Harga Dinamis --}}
                <div class="mt-3 small fw-bold text-danger" id="info_harga_billing">
                    Total yang harus dibayar: <span id="text_total_billing">Rp0</span>
                </div>
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
                    <th>Aksi</th>
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
                    <td>
                        <a href="{{ route('billing.invoice', $r->id) }}" class="btn btn-sm btn-outline-success" target="_blank">
                            <i class="bi bi-file-pdf"></i> PDF
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted">Belum ada riwayat transaksi.</td></tr>
                @endforelse
                
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectPaket = document.getElementById('pilih_paket_billing');
        const selectDurasi = document.getElementById('pilih_durasi_billing');
        const textTotal = document.getElementById('text_total_billing');

        function updateHargaDanDurasi() {
            if (!selectPaket || !selectDurasi || !textTotal) return;

            const opt = selectPaket.options[selectPaket.selectedIndex];
            const hargaBulanan = parseFloat(opt.getAttribute('data-harga-bulanan')) || 0;
            const hargaTahunan = parseFloat(opt.getAttribute('data-harga-tahunan')) || 0;
            const durasiOtomatis = opt.getAttribute('data-durasi') || 'bulanan';

            // 1. Set durasi otomatis berdasarkan paket
            selectDurasi.value = durasiOtomatis;

            // 2. Hitung total harga
            let total = 0;
            if (durasiOtomatis === 'tahunan') {
                total = hargaTahunan;
            } else {
                total = hargaBulanan;
            }

            textTotal.innerText = 'Rp' + new Intl.NumberFormat('id-ID').format(total);
        }

        if (selectPaket) selectPaket.addEventListener('change', updateHargaDanDurasi);
        
        // Panggil saat pertama kali load
        updateHargaDanDurasi();
    });
</script>
@endpush
@endsection