@extends('layouts.app')

@section('title', 'Rekapan SPJ')
@section('nav', 'spj-rekap')

@push('styles')
<style>
    /* Animasi Fade In Up */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-up {
        animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        opacity: 0;
    }
    
    .delay-1 { animation-delay: 0.1s; }
    .delay-2 { animation-delay: 0.2s; }
    .delay-3 { animation-delay: 0.3s; }

    /* Card Rekap Premium */
    .card-rekap {
        border: none;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        z-index: 1;
    }
    
    .card-rekap::before {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 4px; height: 100%;
        background: linear-gradient(to bottom, var(--hijau-500, #10b981), var(--hijau-800, #065f46));
        z-index: -1;
        transition: width 0.3s ease;
    }

    .card-rekap:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(6, 78, 59, 0.08);
    }
    
    .card-rekap:hover::before {
        width: 100%;
        opacity: 0.05;
    }

    .card-rekap .card-title {
        color: var(--text-secondary, #64748b);
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
    }

    .card-rekap .card-amount {
        color: var(--hijau-900, #064e3b);
        font-weight: 800;
        font-size: 1.6rem;
        margin: 0.5rem 0;
        font-feature-settings: "tnum"; /* Agar angka rata */
    }

    .card-rekap .card-link {
        display: inline-flex;
        align-items: center;
        color: var(--hijau-600, #059669);
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        transition: gap 0.2s;
    }

    .card-rekap .card-link:hover {
        gap: 5px; /* Animasi panah bergeser */
    }

    /* Grand Total Box */
    .grand-total-box {
        background: linear-gradient(135deg, var(--hijau-900, #064e3b), var(--hijau-700, #047857));
        color: #ffffff;
        border-radius: 16px;
        padding: 1.5rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 10px 25px -5px rgba(6, 78, 59, 0.4);
        position: relative;
        overflow: hidden;
    }
    
    .grand-total-box::after {
        content: '\F4D6'; /* Bootstrap Icon receipt */
        font-family: 'bootstrap-icons';
        position: absolute;
        right: -20px; bottom: -20px;
        font-size: 8rem;
        opacity: 0.1;
        transform: rotate(-15deg);
        pointer-events: none;
    }

    .grand-total-box .label {
        font-size: 1.1rem;
        font-weight: 500;
        opacity: 0.9;
    }

    .grand-total-box .amount {
        font-size: 2.2rem;
        font-weight: 800;
        color: var(--kuning-400, #facc15);
        font-feature-settings: "tnum";
    }

    /* Styling Select & Button agar selaras */
    .form-select-custom {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background-color: #f8fafc;
        font-weight: 500;
        padding: 0.5rem 1rem;
        transition: all 0.2s;
    }
    .form-select-custom:focus {
        border-color: var(--hijau-500, #10b981);
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-end mb-4 animate-up delay-1">
        <div>
            <h1 class="h3 mb-1 fw-bold" style="color: var(--hijau-900, #064e3b);">Rekapan SPJ</h1>
            <p class="text-secondary mb-0 small">
                <i class="bi bi-calendar3 me-1"></i> Tahun Anggaran <strong>{{ $tahun }}</strong>
                @if($bulan) &middot; <strong>{{ $daftarBulan[$bulan] }}</strong> @endif
            </p>
        </div>
        <a href="{{ route('spj.rekap.ekspor', request()->only('tahun','bulan')) }}"
           class="btn btn-outline-daun shadow-sm" style="border-radius: 10px;">
            <i class="bi bi-download me-1"></i> Ekspor CSV
        </a>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm mb-4 animate-up delay-2" style="border-radius: 16px;">
        <div class="card-body p-3">
            <form method="get" class="row g-2 align-items-center">
                <div class="col-auto">
                    <label class="small text-secondary fw-bold mb-1 d-block">Tahun</label>
                    <select name="tahun" class="form-select form-select-custom form-select-sm">
                        @for ($t = date('Y'); $t >= date('Y') - 5; $t--)
                            <option value="{{ $t }}" @selected($tahun == $t)>{{ $t }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto">
                    <label class="small text-secondary fw-bold mb-1 d-block">Bulan</label>
                    <select name="bulan" class="form-select form-select-custom form-select-sm">
                        <option value="">Semua bulan</option>
                        @foreach ($daftarBulan as $n => $label)
                            <option value="{{ $n }}" @selected($bulan == $n)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto align-self-end">
                    <button class="btn btn-primary px-4" style="border-radius: 10px; background: var(--hijau-800); border: none;">
                        <i class="bi bi-search me-1"></i> Tampilkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Cards Ringkasan --}}
    <div class="row g-3 mb-4">
        @forelse ($ringkasan as $jenis => $r)
            @php
                // Pilih ikon berdasarkan nama kategori
                $ikon = match($jenis) {
                    'Perjalanan Dinas' => 'bi-airplane',
                    'Makan dan Minum' => 'bi-cup-hot',
                    'Belanja ATK' => 'bi-pencil-square',
                    'Honorarium & Jasa' => 'bi-people',
                    'Belanja Pemeliharaan' => 'bi-tools',
                    'Jasa & Sewa' => 'bi-building-gear',
                    'Pakaian & Seragam' => 'bi-person-badge',
                    'Bahan Bakar & Suku Cadang' => 'bi-fuel-pump',
                    'Iuran & Pajak' => 'bi-receipt-cutoff',
                    'Hadiah & Penghargaan' => 'bi-trophy',
                    default => 'bi-box-seam',
                };
            @endphp
            <div class="col-md-4 col-lg-3 animate-up delay-3" style="animation-delay: {{ $loop->index * 0.1 + 0.3 }}s;">
                <div class="card card-rekap h-100">
                    <div class="card-body p-4">
                        <div class="card-title d-flex justify-content-between align-items-center">
                            <span>{{ $jenis }}</span>
                            <i class="bi {{ $ikon }} text-success opacity-75 fs-5"></i>
                        </div>
                        <div class="card-amount">
                            <span class="fs-6 text-secondary fw-normal">Rp</span> {{ number_format($r['total'], 0, ',', '.') }}
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="badge bg-light text-secondary border px-2 py-1">
                                {{ $r['jumlah'] }} transaksi
                            </span>
                            <a href="{{ route('spj.rekap.detail', array_merge(request()->only('tahun','bulan'), ['jenis' => $jenis])) }}"
                            class="card-link">
                                Lihat detail <i class="bi bi-arrow-right-short"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 animate-up">
                <div class="alert alert-info border-0 shadow-sm" style="border-radius: 12px;">
                    <i class="bi bi-info-circle me-2"></i> Belum ada data SPJ (bukan draft) pada periode ini.
                </div>
            </div>
        @endforelse
    </div>

    {{-- Grand Total --}}
    @if(count($ringkasan) > 0)
    <div class="grand-total-box animate-up delay-3">
        <div>
            <div class="label"><i class="bi bi-calculator me-2"></i> Grand Total Pengeluaran</div>
            <div class="small opacity-75 mt-1">Akumulasi dari {{ $ringkasan->sum('jumlah') }} transaksi</div>
        </div>
        <div class="amount">
            <span class="fs-4 me-1">Rp</span>{{ number_format($grandTotal, 0, ',', '.') }}
        </div>
    </div>
    @endif
</div>
@endsection