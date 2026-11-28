@extends('layouts.app')

@section('title', 'Detail Rekap SPJ')
@section('nav', 'spj-rekap')

@push('styles')
<style>
    /* Animasi Slide Down untuk Tabel */
    @keyframes slideDownFade {
        0% { opacity: 0; transform: translateY(-15px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    
    /* Animasi Baris Tabel Muncul Bergantian */
    @keyframes rowFadeIn {
        0% { opacity: 0; transform: translateX(-10px); }
        100% { opacity: 1; transform: translateX(0); }
    }

    .animate-table {
        animation: slideDownFade 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    .row-animate {
        opacity: 0;
        animation: rowFadeIn 0.4s ease-out forwards;
    }

    /* Card Container */
    .card-premium-table {
        border: none;
        border-radius: 20px;
        background: #ffffff;
        box-shadow: 0 10px 40px -10px rgba(6, 78, 59, 0.08);
        overflow: hidden;
        border: 1px solid rgba(6, 78, 59, 0.05);
    }

    /* Tabel Kustom */
    .table-premium {
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }

    /* Header Tabel */
    .table-premium thead th {
        background: linear-gradient(to bottom, #f8fafc, #f1f5f9);
        color: #475569;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        font-weight: 800;
        padding: 1.25rem 1rem;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    /* Baris Tabel */
    .table-premium tbody td {
        padding: 1.1rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 0.9rem;
        transition: all 0.25s ease;
        position: relative;
    }

    /* Efek Zebra (Garis-Garis Halus) */
    .table-premium tbody tr:nth-child(even) {
        background-color: #fcfdfd;
    }

    /* Hover Effect pada Baris */
    .table-premium tbody tr:hover td {
        background-color: #f0fdf4; /* Hijau sangat muda */
        color: #064e3b;
    }

    /* Garis aksen kiri saat di-hover */
    .table-premium tbody tr td:first-child {
        border-left: 3px solid transparent;
        transition: border-color 0.25s ease;
    }
    .table-premium tbody tr:hover td:first-child {
        border-left-color: var(--hijau-500, #10b981);
    }

    /* Nomor Urut */
    .trx-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #475569;
        font-weight: 700;
        font-size: 0.85rem;
        transition: all 0.25s ease;
    }
    .table-premium tbody tr:hover .trx-number {
        background: var(--hijau-600, #059669);
        color: white;
        transform: scale(1.1);
        box-shadow: 0 4px 10px rgba(5, 150, 105, 0.3);
    }

    /* Kode Rekening Badge */
    .badge-rekening {
        font-family: 'Courier New', Courier, monospace;
        background-color: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        padding: 0.4em 0.6em;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: -0.5px;
        transition: all 0.2s;
    }
    .table-premium tbody tr:hover .badge-rekening {
        background-color: #ffffff;
        border-color: #cbd5e1;
        color: var(--hijau-800, #065f46);
    }

    /* Status Badge (Dynamic) */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 0.4em 0.8em;
        font-size: 0.75rem;
        font-weight: 700;
        border-radius: 50rem;
        white-space: nowrap;
    }
    .status-pill.menunggu { background: #fef3c7; color: #b45309; }
    .status-pill.disetujui { background: #d1fae5; color: #047857; }
    .status-pill.draft { background: #f1f5f9; color: #475569; }
    .status-pill.dibatalkan { background: #fee2e2; color: #b91c1c; }

    /* Total Harga Kolom */
    .col-total {
        font-weight: 800;
        font-size: 1rem;
        color: var(--hijau-900, #064e3b);
        font-feature-settings: "tnum";
    }

    /* Tombol Aksi Buka */
    .btn-action {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: white;
        border: 1px solid #e2e8f0;
        color: #64748b;
        transition: all 0.2s ease;
        text-decoration: none;
    }
    .btn-action:hover {
        background: var(--hijau-900, #064e3b);
        color: white;
        border-color: var(--hijau-900, #064e3b);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(6, 78, 59, 0.3);
    }

    /* Footer Total */
    .table-premium tfoot td {
        padding: 1.5rem 1rem;
        background-color: #ffffff;
        border-top: 2px dashed #cbd5e1;
    }
    .total-label {
        font-size: 1.1rem;
        font-weight: 700;
        color: #64748b;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .total-amount {
        font-size: 1.8rem;
        font-weight: 900;
        background: linear-gradient(135deg, var(--hijau-900, #064e3b), var(--hijau-500, #10b981));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-feature-settings: "tnum";
    }

    /* Tombol Kembali */
    .btn-back-premium {
        background: white;
        border: 1px solid #e2e8f0;
        color: #475569;
        border-radius: 12px;
        padding: 0.6rem 1.2rem;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
    }
    .btn-back-premium:hover {
        background: #f8fafc;
        color: var(--hijau-900, #064e3b);
        transform: translateX(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    {{-- Header Halaman --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 animate-table">
        <div class="mb-3 mb-md-0">
            <nav aria-label="breadcrumb" class="mb-2">
                <ol class="breadcrumb mb-0" style="font-size: 0.85rem;">
                    <li class="breadcrumb-item"><a href="{{ route('spj.rekap.index', ['tahun' => $tahun, 'bulan' => $bulan]) }}" class="text-decoration-none text-secondary">Rekapan SPJ</a></li>
                    <li class="breadcrumb-item active" aria-current="page" style="color: var(--hijau-700);">{{ $jenis }}</li>
                </ol>
            </nav>
            <h1 class="h2 mb-1 fw-bolder" style="color: var(--hijau-900, #064e3b); letter-spacing: -0.5px;">
                Detail Transaksi: <span style="color: var(--hijau-600, #059669);">{{ $jenis }}</span>
            </h1>
            <p class="text-secondary mb-0">
                <i class="bi bi-calendar-check me-1"></i> Tahun Anggaran {{ $tahun }}
                @if($bulan) &middot; Periode Bulan ke-{{ $bulan }} @endif
            </p>
        </div>
        <div>
            <a href="{{ route('spj.rekap.index', ['tahun' => $tahun, 'bulan' => $bulan]) }}" class="btn btn-back-premium">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Rekap
            </a>
        </div>
    </div>

    {{-- Card Utama Tabel --}}
    <div class="card card-premium-table animate-table" style="animation-delay: 0.1s;">
        <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
            <table class="table table-premium mb-0">
                <thead>
                    <tr>
                        <th width="60" class="text-center">No</th>
                        <th>Tanggal</th>
                        <th>Nomor SPJ</th>
                        <th>Uraian Pekerjaan</th>
                        <th>Rekening</th>
                        <th>Sumber Dana</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Total (Rp)</th>
                        <th width="60" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $i => $s)
                        @php
                            // Warna badge status
                            $statusClass = 'draft';
                            $statusIcon = 'bi-file-earmark';
                            $statusLower = strtolower($s->status->label());
                            
                            if(str_contains($statusLower, 'menunggu') || str_contains($statusLower, 'proses')) {
                                $statusClass = 'menunggu'; $statusIcon = 'bi-hourglass-split';
                            } elseif(str_contains($statusLower, 'setuju') || str_contains($statusLower, 'final')) {
                                $statusClass = 'disetujui'; $statusIcon = 'bi-check-circle-fill';
                            } elseif(str_contains($statusLower, 'batal') || str_contains($statusLower, 'tolak')) {
                                $statusClass = 'dibatalkan'; $statusIcon = 'bi-x-circle-fill';
                            }
                        @endphp
                        <tr class="row-animate" style="animation-delay: {{ 0.1 + ($i * 0.05) }}s;">
                            <td class="text-center">
                                <span class="trx-number">{{ $i + 1 }}</span>
                            </td>
                            <td class="text-nowrap">
                                <i class="bi bi-calendar3 text-muted me-1" style="font-size: 0.8rem;"></i>
                                {{ $s->tanggal?->format('d/m/Y') }}
                            </td>
                            <td>
                                <span class="fw-bold" style="color: var(--hijau-700);">
                                    {{ $s->nomor ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <div class="text-wrap" style="max-width: 300px; line-height: 1.4;">
                                    {{ $s->uraian }}
                                </div>
                            </td>
                            <td>
                                <span class="badge-rekening" data-bs-toggle="tooltip" title="{{ $s->anggaranRekening?->rekening?->uraian ?? 'Nama rekening tidak tersedia' }}">
                                    <i class="bi bi-hash"></i>{{ $s->anggaranRekening?->rekening?->kode }}
                                </span>
                            </td>
                            <td>
                                <span class="text-secondary fw-medium" style="font-size: 0.85rem;">
                                    <i class="bi bi-wallet2 me-1"></i> {{ $s->sumberDana?->nama }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="status-pill {{ $statusClass }}">
                                    <i class="bi {{ $statusIcon }}"></i> {{ $s->status->label() }}
                                </span>
                            </td>
                            <td class="text-end col-total">
                                {{ number_format((float) $s->total, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                <a href="{{ route('spj.show', $s) }}" class="btn-action" data-bs-toggle="tooltip" title="Buka Detail SPJ">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center justify-content-center">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                        <i class="bi bi-inbox fs-1 text-secondary opacity-50"></i>
                                    </div>
                                    <h5 class="text-secondary fw-bold mb-1">Belum Ada Data</h5>
                                    <p class="text-muted small">Tidak ada transaksi untuk kategori ini pada periode yang dipilih.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($rows) > 0)
                <tfoot>
                    <tr>
                        <td colspan="7" class="text-end">
                            <span class="total-label"><i class="bi bi-calculator me-2"></i>Total Pengeluaran</span>
                        </td>
                        <td class="text-end">
                            <div class="total-amount">
                                <span class="fs-5 me-1">Rp</span>{{ number_format($total, 0, ',', '.') }}
                            </div>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Inisialisasi Tooltip Bootstrap
    document.addEventListener("DOMContentLoaded", function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                placement: 'top',
                animation: true,
                delay: { show: 300, hide: 100 }
            })
        })
    });
</script>
@endpush