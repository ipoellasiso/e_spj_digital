@extends('layouts.app')

@section('title', 'Realisasi Per Sumber Dana')
@section('nav', 'realisasi')

@push('styles')
<style>
    .matrix-table-wrapper {
        overflow-x: auto;
        overflow-y: auto;
        max-height: 75vh;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
    }
    .matrix-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        font-size: 0.85rem;
    }
    
    /* --- HEADER TABEL (Semua Hijau Gelap) --- */
    .matrix-table thead th {
        position: sticky;
        top: 0;
        background-color: #065f46; 
        color: #ffffff;
        font-weight: 600;
        padding: 12px 10px;
        text-align: center;
        border-right: 1px solid #047857;
        border-bottom: 2px solid #047857;
        z-index: 20;
        white-space: nowrap;
    }
    
    /* --- STICKY KOLOM KIRI (Kode & Uraian) --- */
    .matrix-table thead th.sticky-col, 
    .matrix-table tbody td.sticky-col {
        position: sticky;
        z-index: 30;
        background-color: inherit;
    }
    .matrix-table thead th.sticky-col { 
        z-index: 40; 
        background-color: #065f46;
        text-align: left;
        padding-left: 12px;
    }

    /* --- UKURAN KOLOM (DIPERBESAR) --- */
    /* Kode: Diperlebar sedikit */
    .matrix-table th.col-kode, .matrix-table td.col-kode { 
        min-width: 140px; max-width: 140px; 
    }
    
    /* Uraian: Diperlebar dan di-wrap agar rapi */
    .matrix-table th.col-uraian, .matrix-table td.col-uraian { 
        min-width: 350px; max-width: 450px; 
        white-space: normal; 
        line-height: 1.4;
    }
    
    /* Pagu & Persen */
    .matrix-table th.col-pagu, .matrix-table td.col-pagu { min-width: 140px; text-align: right; }
    .matrix-table th.col-persen, .matrix-table td.col-persen { min-width: 80px; text-align: right; }
    
    /* Kolom Sumber Dana: Lebar seragam, teks kanan */
    .matrix-table th.col-dana, .matrix-table td.col-dana { 
        min-width: 120px; 
        text-align: right; 
    }

    /* --- BODY TABEL --- */
    .matrix-table tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        border-right: 1px solid #f1f5f9;
        color: #334155;
        vertical-align: middle;
    }
    
    /* Warna Baris Level */
    .tr-program { background-color: #f0fdf4 !important; font-weight: bold; }
    .tr-kegiatan { background-color: #f8fafc !important; font-weight: bold; }
    .tr-sub { background-color: #ffffff !important; font-weight: 600; color: #047857 !important; }
    .tr-rekening { background-color: #ffffff; }
    
    .tr-rekening.hidden-row { display: none; }
    
    /* --- TOMBOL EXPAND --- */
    .btn-expand {
        background: none; border: none; padding: 0; color: #64748b;
        cursor: pointer; margin-right: 4px; width: 16px; vertical-align: middle;
    }
    .btn-expand i { transition: transform 0.2s; font-size: 0.75rem; }
    
    /* --- STICKY FOOTER --- */
    .matrix-table tfoot td {
        position: sticky;
        bottom: 0;
        background-color: #f1f5f9;
        font-weight: bold;
        border-top: 2px solid #065f46;
        z-index: 20;
        padding: 12px 10px;
        color: #064e3b;
    }
    .matrix-table tfoot td.sticky-col { z-index: 30; }

    /* --- GAYA ANGKA --- */
    .angka-sumber-dana {
        font-family: 'Courier New', Courier, monospace;
        font-weight: 700;
        color: #0f172a; 
        font-size: 0.85rem;
    }
    .angka-kosong {
        color: #cbd5e1;
        font-weight: normal;
    }
    .teks-proses {
        display: block;
        font-family: 'Courier New', Courier, monospace;
        font-size: 0.7rem;
        color: #d97706;
        font-weight: 600;
        margin-top: 2px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
            <div>
                <h1 class="h4 fw-bold mb-1" style="color:var(--hutan)">Realisasi Anggaran Belanja Per Sumber Dana</h1>
                <p class="text-secondary mb-0">Tahun Anggaran {{ $tahun }}. Sampai dengan <strong>{{ $sampai->translatedFormat('d F Y') }}</strong>.</p>
            </div>
            {{-- TAMBAHKAN TOMBOL INI --}}
            @if ($opd && $hasil && $hasil['versi'])
                @php
                    $parameter = array_merge($kueri, ['sampai' => $sampai->toDateString(), 'tahun' => $tahun, 'opd' => $opd?->id]);
                @endphp
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('laporan.realisasi-sumber-dana.excel', $parameter) }}" class="btn btn-outline-daun"><i class="bi bi-file-earmark-excel me-1"></i> Excel</a>
                    <a href="{{ route('laporan.realisasi-sumber-dana.pdf', $parameter) }}" target="_blank" rel="noopener" class="btn btn-kuning"><i class="bi bi-file-earmark-pdf me-1"></i> PDF</a>
                </div>
            @endif
        </div>
    </div>

    @include('laporan._filter', ['aksi' => 'laporan.realisasi-sumber-dana', 'mode' => 'sampai'])

    @if (! $opd)
        <section class="panel p-4 text-secondary">Pilih OPD di atas untuk melihat laporannya.</section>
    @elseif (! $hasil['versi'])
        <section class="panel p-4"><strong>DPA tahun {{ $tahun }} belum diimpor.</strong></section>
    @else
        @php $t = $hasil['total']; @endphp
        
        <div class="d-flex gap-2 mb-2">
            <button type="button" class="btn btn-sm btn-outline-daun" id="btnBuka"><i class="bi bi-arrows-expand"></i> Buka Semua</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnTutup"><i class="bi bi-arrows-collapse"></i> Tutup Semua</button>
        </div>

        <div class="matrix-table-wrapper">
            <table class="matrix-table">
                <thead>
                    <tr>
                        <th class="col-kode sticky-col" style="left: 0;">Kode</th>
                        {{-- Perhatikan left: 140px karena col-kode sekarang 140px --}}
                        <th class="col-uraian sticky-col" style="left: 140px;">Uraian</th>
                        <th class="col-pagu">Pagu (Rp)</th>
                        @foreach ($hasil['sumber_dana'] as $sd)
                            <th class="col-dana">{{ $sd->nama }}</th>
                        @endforeach
                        <th class="col-persen">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($hasil['baris'] as $b)
                        @php
                            $isRek = $b['level'] === 'rekening';
                            $trClass = 'tr-' . $b['level'] . ($isRek ? ' hidden-row' : '');
                            $indent = $isRek ? 'padding-left: 35px' : '';
                        @endphp
                        <tr class="{{ $trClass }}" @if($isRek) data-induk="{{ $b['induk'] }}" @endif>
                            <td class="sticky-col col-kode fw-medium text-dark" style="left: 0;">
                                {{ $b['kode'] }}
                            </td>
                            {{-- Perhatikan left: 140px karena col-kode sekarang 140px --}}
                            <td class="sticky-col col-uraian" style="left: 140px; {{ $indent }}">
                                @if($b['level'] === 'program' || $b['level'] === 'kegiatan' || $b['level'] === 'sub')
                                    <button type="button" class="btn-expand btn-toggle" data-kode="{{ $b['kode'] }}" aria-expanded="false">
                                        <i class="bi bi-plus-square-fill text-success"></i>
                                    </button>
                                @endif
                                {{ $b['uraian'] }}
                            </td>
                            <td class="col-pagu fw-semibold">{{ number_format($b['pagu']/100, 2, ',', '.') }}</td>
                            
                            @foreach ($hasil['sumber_dana'] as $sd)
                                @php
                                    $nilaiFinal = $b['dana'][$sd->id][0] ?? 0;
                                    $nilaiProses = $b['dana'][$sd->id][1] ?? 0;
                                    $totalDana = $nilaiFinal + $nilaiProses;
                                @endphp
                                <td class="col-dana">
                                    @if($totalDana > 0)
                                        <span class="angka-sumber-dana">{{ number_format($nilaiFinal/100, 2, ',', '.') }}</span>
                                        @if($nilaiProses > 0)
                                            <span class="teks-proses">({{ number_format($nilaiProses/100, 0, ',', '.') }})</span>
                                        @endif
                                    @else
                                        <span class="angka-kosong">-</span>
                                    @endif
                                </td>
                            @endforeach
                            
                            <td class="col-persen fw-bold text-secondary">{{ number_format($b['persen'], 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="sticky-col text-end" style="left:0; padding-right:15px; color:#065f46;">TOTAL KESELURUHAN</td>
                        <td class="col-pagu text-dark">{{ number_format($t['pagu']/100, 2, ',', '.') }}</td>
                        @foreach ($hasil['sumber_dana'] as $sd)
                            @php
                                $nilaiFinal = $t['dana'][$sd->id][0] ?? 0;
                                $nilaiProses = $t['dana'][$sd->id][1] ?? 0;
                            @endphp
                            <td class="col-dana">
                                @if($nilaiFinal > 0)
                                    <span class="angka-sumber-dana" style="color: #065f46;">{{ number_format($nilaiFinal/100, 2, ',', '.') }}</span>
                                @else
                                    <span class="angka-kosong">-</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="col-persen text-dark">{{ number_format($t['pagu'] > 0 ? (array_sum(array_column($t['dana'], 0)) / $t['pagu']) * 100 : 0, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function atur(kode, buka) {
            document.querySelectorAll(`tr.tr-rekening[data-induk="${kode}"]`).forEach(tr => {
                tr.classList.toggle('hidden-row', !buka);
            });
            
            var btn = document.querySelector(`button.btn-toggle[data-kode="${kode}"]`);
            if (btn) {
                btn.setAttribute('aria-expanded', buka);
                var icon = btn.querySelector('i');
                icon.className = buka ? 'bi bi-dash-square-fill text-danger' : 'bi bi-plus-square-fill text-success';
            }
        }

        document.addEventListener('click', function(e) {
            var btn = e.target.closest('button.btn-toggle');
            if(btn) {
                var kode = btn.dataset.kode;
                var isBuka = btn.getAttribute('aria-expanded') === 'true';
                atur(kode, !isBuka);
            }
        });

        document.getElementById('btnBuka').addEventListener('click', function() {
            document.querySelectorAll('button.btn-toggle').forEach(btn => atur(btn.dataset.kode, true));
        });

        document.getElementById('btnTutup').addEventListener('click', function() {
            document.querySelectorAll('button.btn-toggle').forEach(btn => atur(btn.dataset.kode, false));
        });
    });
</script>
@endpush