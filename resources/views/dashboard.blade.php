@extends('layouts.app')

@section('title', 'Beranda')
@section('nav', 'beranda')

@push('styles')
<style>
    /* =========================================
       DASHBOARD LIGHT PREMIUM THEME
       ========================================= */
    
    /* Latar belakang utama dashboard dibuat gradien putih bersih elegan */
    .dashboard-wrapper {
        background: radial-gradient(circle at 10% 20%, #ffffff 0%, #f0fdf4 50%, #f8fafc 100%);
        border-radius: 24px;
        padding: 32px;
        margin: -12px; 
        color: var(--text-primary);
        min-height: 100vh;
        position: relative;
        overflow: hidden;
    }

    /* Partikel dekoratif di belakang (pastel blur) */
    .dashboard-wrapper::before, .dashboard-wrapper::after {
        content: ""; position: absolute; border-radius: 50%; filter: blur(120px); z-index: 0; opacity: 0.6;
    }
    .dashboard-wrapper::before {
        width: 400px; height: 400px; background: rgba(16, 185, 129, 0.15); top: -100px; left: -100px;
        animation: float-blob 10s ease-in-out infinite alternate;
    }
    .dashboard-wrapper::after {
        width: 300px; height: 300px; background: rgba(251, 191, 36, 0.15); bottom: -50px; right: -50px;
        animation: float-blob 8s ease-in-out infinite alternate-reverse;
    }

    .dashboard-content {
        position: relative; z-index: 1;
    }

    /* ===== ANIMASI DASAR ===== */
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes float-blob {
        0% { transform: translate(0, 0) scale(1); }
        100% { transform: translate(50px, 50px) scale(1.2); }
    }
    @keyframes float-card {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-6px); }
    }
    @keyframes pulse-glow-hijau {
        0%, 100% { box-shadow: 0 0 15px rgba(16, 185, 129, 0.2); }
        50% { box-shadow: 0 0 30px rgba(16, 185, 129, 0.4); }
    }
    @keyframes text-shine {
        to { background-position: 200% center; }
    }

    .animate-up {
        animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    /* ===== TEKS GRADIENT ===== */
    .text-gradient-hijau {
        background: linear-gradient(90deg, #059669, #10b981, #34d399);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-size: 200% auto;
        animation: text-shine 3s linear infinite;
    }

    /* ===== KARTU KPI (LIGHT GLASS) ===== */
    .card-kpi-light {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.9);
        border-radius: 24px;
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 20px;
        position: relative;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05), 0 1px 3px rgba(0,0,0,0.02);
        z-index: 1;
    }
    
    /* Efek Kilau saat Hover */
    .card-kpi-light::before {
        content: ""; position: absolute; top: 0; left: -100%; width: 50%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.8), transparent);
        transform: skewX(-25deg); transition: 0.5s; z-index: -1;
    }
    .card-kpi-light:hover::before { left: 150%; }
    .card-kpi-light:hover {
        transform: translateY(-8px);
        border-color: rgba(16, 185, 129, 0.3);
        box-shadow: 0 20px 40px -10px rgba(16, 185, 129, 0.15);
        animation: float-card 3s ease-in-out infinite;
    }

    .kpi-icon-light {
        width: 64px; height: 64px;
        border-radius: 18px;
        display: grid; place-items: center;
        font-size: 28px;
        flex-shrink: 0;
        position: relative;
        transition: transform 0.3s;
    }
    .card-kpi-light:hover .kpi-icon-light {
        transform: scale(1.15) rotate(10deg);
    }
    
    /* Warna Pastel Ceria untuk Ikon */
    .kpi-pagu-light { 
        background: linear-gradient(135deg, #e0e7ff, #c7d2fe); color: #4f46e5; border: 1px solid rgba(79, 70, 229, 0.2);
    }
    .kpi-realisasi-light { 
        background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #047857; border: 1px solid rgba(4, 120, 87, 0.2);
        animation: pulse-glow-hijau 3s infinite;
    }
    .kpi-proses-light { 
        background: linear-gradient(135deg, #fef3c7, #fde68a); color: #b45309; border: 1px solid rgba(180, 83, 9, 0.2);
    }

    .kpi-label-light {
        font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: var(--text-secondary);
    }
    .kpi-value-light {
        font-size: 28px; font-weight: 800; margin: 4px 0 0; color: var(--text-primary); letter-spacing: -0.5px;
    }

    /* ===== KARTU PANEL (CHART & LIST) ===== */
    .panel-light {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 24px;
        overflow: hidden;
        height: 100%;
        transition: border-color 0.3s, box-shadow 0.3s;
        box-shadow: 0 10px 30px -10px rgba(0,0,0,0.03);
    }
    .panel-light:hover { 
        border-color: rgba(16, 185, 129, 0.3); 
        box-shadow: 0 15px 35px -10px rgba(16, 185, 129, 0.1);
    }

    .panel-header-light {
        padding: 20px 24px;
        border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        display: flex; justify-content: space-between; align-items: center;
        font-weight: 700; color: var(--text-primary); font-size: 15px;
        background: rgba(248, 250, 252, 0.5);
    }

    /* ===== PROGRESS BAR LIGHT ===== */
    .progress-light {
        height: 8px; background: #e2e8f0; border-radius: 999px; overflow: hidden;
    }
    .progress-bar-light {
        height: 100%; border-radius: 999px; width: 0;
        transition: width 1.5s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
    }
    /* Animasi denyut di ujung progress */
    .progress-bar-light::after {
        content: ""; position: absolute; right: 0; top: 0; bottom: 0; width: 20px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.8));
        border-radius: 999px; animation: pulse-end 1.5s infinite;
    }
    @keyframes pulse-end { 0%, 100% { opacity: 0.5; } 50% { opacity: 1; } }

    /* ===== TABEL LIGHT ===== */
    .tabel-light {
        width: 100%; border-collapse: collapse; color: var(--text-primary);
    }
    .tabel-light th {
        background: #f8fafc; padding: 16px 24px;
        font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-secondary);
        border-bottom: 1px solid var(--border-color); text-align: left;
    }
    .tabel-light td {
        padding: 16px 24px; border-bottom: 1px solid var(--border-color);
        font-size: 14px; vertical-align: middle;
    }
    .tabel-light tbody tr { transition: background 0.2s; }
    .tabel-light tbody tr:hover { background: rgba(16, 185, 129, 0.03); }
    .tabel-light tbody tr:last-child td { border-bottom: none; }

    .badge-light {
        padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 800;
        display: inline-flex; align-items: center; gap: 4px; letter-spacing: 0.5px;
    }
    .badge-light-final { background: #d1fae5; color: #047857; border: 1px solid #a7f3d0; }
    .badge-light-diajukan { background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; }
    .badge-light-verifikasi { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }

    /* List Item Antrian */
    .list-light-item {
        padding: 16px 24px; border-bottom: 1px solid var(--border-color);
        display: flex; align-items: center; gap: 16px; transition: all 0.3s;
    }
    .list-light-item:hover { background: #f8fafc; padding-left: 30px; }
    .list-light-item:last-child { border-bottom: none; }

    .btn-icon-light {
        width: 40px; height: 40px; border-radius: 12px;
        background: #fff; border: 1px solid var(--border-color); color: var(--hijau-600);
        display: grid; place-items: center; transition: all 0.3s; text-decoration: none;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
    }
    .btn-icon-light:hover {
        background: var(--hijau-600); color: #fff; border-color: var(--hijau-600);
        transform: scale(1.1) rotate(10deg); box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4);
    }

    /* Tombol Premium */
    .btn-premium-light {
        background: linear-gradient(135deg, #10b981, #047857);
        color: #fff; border: none; border-radius: 12px; padding: 12px 24px;
        font-weight: 700; display: inline-flex; align-items: center; gap: 8px;
        text-decoration: none; transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }
    .btn-premium-light:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 10px 25px rgba(16, 185, 129, 0.5); color: #fff;
    }
</style>
@endpush

@section('content')
    <div class="dashboard-wrapper">
        <div class="dashboard-content">
            {{-- Header Halaman --}}
            <div class="page-header animate-up d-flex justify-content-between align-items-center mb-5" style="animation-delay: 0.1s;">
                <div>
                    <h1 class="fw-bolder mb-1 text-gradient-hijau" style="font-size: 2rem; letter-spacing: -1px;">Dashboard</h1>
                    <p class="mb-0" style="color: var(--text-secondary); font-size: 14px;">
                        <i class="bi bi-calendar3 me-1"></i> Posisi anggaran per {{ now()->translatedFormat('j F Y') }}
                    </p>
                </div>
                <div>
                    <a href="{{ route('spj.create') ?? '#' }}" class="btn-premium-light">
                        <i class="bi bi-plus-lg"></i> Buat SPJ
                    </a>
                </div>
            </div>

            {{-- Baris 1: Kartu KPI --}}
            <div class="row g-4 mb-4">
                <div class="col-md-4 animate-up" style="animation-delay: 0.2s;">
                    <div class="card-kpi-light">
                        <div class="kpi-icon-light kpi-pagu-light">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <div>
                            <div class="kpi-label-light">Pagu Anggaran</div>
                            <h3 class="kpi-value-light angka counter" data-target="{{ $pagu }}">Rp0</h3>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 animate-up" style="animation-delay: 0.3s;">
                    <div class="card-kpi-light">
                        <div class="kpi-icon-light kpi-realisasi-light">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div>
                            <div class="kpi-label-light">Realisasi Final</div>
                            <h3 class="kpi-value-light angka counter" data-target="{{ $realisasi }}">Rp0</h3>
                            <div class="small fw-bold mt-1" style="color: #047857;"><i class="bi bi-graph-up-arrow"></i> {{ $persen_final }}% dari pagu</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 animate-up" style="animation-delay: 0.4s;">
                    <div class="card-kpi-light">
                        <div class="kpi-icon-light kpi-proses-light">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                        <div>
                            <div class="kpi-label-light">SPJ Dalam Proses</div>
                            <h3 class="kpi-value-light angka counter" data-target="{{ $proses }}">Rp0</h3>
                            <div class="small fw-bold mt-1" style="color: #b45309;"><i class="bi bi-clock-history"></i> {{ $persen_proses }}% dari pagu</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Baris 2: Chart & Progress --}}
            <div class="row g-4 mb-4">
                <div class="col-lg-8 animate-up" style="animation-delay: 0.5s;">
                    <div class="panel-light">
                        <div class="panel-header-light">
                            <span><i class="bi bi-bar-chart-line-fill me-2" style="color: var(--hijau-600);"></i>Tren Realisasi Anggaran</span>
                            <select class="form-select form-select-sm border-0 bg-light fw-bold" style="width: auto; color: var(--text-primary);">
                                <option>Tahun {{ now()->year }}</option>
                            </select>
                        </div>
                        <div class="card-body p-4">
                            <canvas id="chartRealisasi" height="100"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 animate-up" style="animation-delay: 0.6s;">
                    <div class="panel-light">
                        <div class="panel-header-light">
                            <span><i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>Pagu Paling Tipis</span>
                        </div>
                        <div class="card-body p-4">
                            @forelse ($pagu_tipis as $item)
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between small mb-2">
                                        <span class="fw-bold" style="color: var(--text-primary);">{{ $item['nama'] }}</span>
                                        <span class="angka fw-bold" style="color: {{ $item['persen'] <= 10 ? '#ef4444' : 'var(--hijau-600)' }};">sisa {{ $item['persen'] }}%</span>
                                    </div>
                                    <div class="progress-light">
                                        <div class="progress-bar-light" 
                                             style="--target-width: {{ 100 - $item['persen'] }}%; background:{{ $item['persen'] <= 10 ? 'linear-gradient(90deg, #f87171, #ef4444)' : 'linear-gradient(90deg, #34d399, #059669)' }}"></div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4" style="color: var(--text-secondary);">
                                    <i class="bi bi-shield-check fs-1 mb-2 d-block" style="color: var(--hijau-600);"></i>
                                    <span class="fw-semibold">Semua pagu dalam kondisi aman.</span>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Baris 3: Tabel & Antrian --}}
            <div class="row g-4">
                <div class="col-xl-7 animate-up" style="animation-delay: 0.7s;">
                    <div class="panel-light">
                        <div class="panel-header-light">
                            <span><i class="bi bi-clock-history me-2" style="color: #3b82f6;"></i>SPJ Terbaru</span>
                            <a href="{{ route('spj.index') ?? '#' }}" class="text-decoration-none small fw-bold" style="color: var(--hijau-600);">Lihat Semua <i class="bi bi-arrow-right"></i></a>
                        </div>
                        <div class="table-responsive">
                            <table class="tabel-light">
                                <thead>
                                    <tr>
                                        <th>Nomor</th>
                                        <th>Uraian</th>
                                        <th class="text-end">Jumlah</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="angka">
                                    @foreach($spj_terbaru as $spj)
                                    <tr>
                                        <td class="fw-bold" style="color: var(--text-secondary);">#{{ $spj['no'] }}</td>
                                        <td>
                                            <div class="fw-bold" style="color: var(--text-primary);">{{ $spj['uraian'] }}</div>
                                            @if($spj['kode'])<div class="small" style="color: var(--text-secondary);">{{ $spj['kode'] }}</div>@endif
                                        </td>
                                        <td class="text-end fw-bold" style="color: var(--text-primary);">Rp{{ number_format($spj['jumlah'], 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            @if($spj['status'] == 'Final')
                                                <span class="badge-light badge-light-final"><i class="bi bi-check-circle-fill"></i> Final</span>
                                            @elseif($spj['status'] == 'Diajukan')
                                                <span class="badge-light badge-light-diajukan"><i class="bi bi-send-fill"></i> Diajukan</span>
                                            @else
                                                <span class="badge-light badge-light-verifikasi"><i class="bi bi-hourglass-split"></i> Verifikasi</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-xl-5 animate-up" style="animation-delay: 0.8s;">
                    <div class="panel-light">
                        <div class="panel-header-light">
                            <span><i class="bi bi-inbox-fill me-2" style="color: #ef4444;"></i>Menunggu Verifikasi</span>
                            <span class="badge bg-danger rounded-pill shadow-sm" style="animation: pulse-glow-hijau 2s infinite;">{{ count($antrian) }} SPJ</span>
                        </div>
                        <ul class="list-unstyled m-0">
                            @foreach ($antrian as $item)
                                <li class="list-light-item">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold" style="color: var(--text-primary);">{{ $item['uraian'] }}</div>
                                        <div class="small angka" style="color: var(--text-secondary);">
                                            No. {{ $item['no'] }} &middot; <span style="color: #d97706; font-weight: bold;">{{ $item['jumlah'] }}</span> &middot; {{ $item['kapan'] }}
                                        </div>
                                    </div>
                                    <a href="{{ route('spj.antrian') ?? '#' }}" class="btn-icon-light" title="Verifikasi">
                                        <i class="bi bi-check2-square fs-5"></i>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // --- 1. ANIMASI COUNTER ANGKA (Lebih Smooth) ---
            const counters = document.querySelectorAll('.counter');
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-target');
                if(target === 0) return; 
                
                const duration = 2000;
                const startTime = performance.now();
                
                const updateCounter = (currentTime) => {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const easeOut = 1 - Math.pow(1 - progress, 4); // Ease out quart
                    
                    const current = target * easeOut;
                    counter.innerText = 'Rp' + new Intl.NumberFormat('id-ID').format(Math.ceil(current));
                    
                    if (progress < 1) {
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.innerText = 'Rp' + new Intl.NumberFormat('id-ID').format(target);
                    }
                };
                requestAnimationFrame(updateCounter);
            });

            // --- 2. ANIMASI PROGRESS BAR ---
            const progressBars = document.querySelectorAll('.progress-bar-light');
            setTimeout(() => {
                progressBars.forEach(bar => {
                    const targetWidth = bar.style.getPropertyValue('--target-width');
                    bar.style.width = targetWidth;
                });
            }, 600);

            // --- 3. CHART.JS LIGHT THEME ---
            var ctx = document.getElementById('chartRealisasi').getContext('2d');
            
            // Gradien Hijau Lembut
            var gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(16, 185, 129, 0.3)'); 
            gradient.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    datasets: [{
                        label: 'Realisasi',
                        data: @json($chart_data),
                        borderColor: '#10b981', // Hijau elegan
                        backgroundColor: gradient,
                        borderWidth: 3,
                        pointBackgroundColor: '#fff', 
                        pointBorderColor: '#10b981',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 8,
                        pointHoverBackgroundColor: '#10b981',
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 3,
                        fill: true,
                        tension: 0.45
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 2500,
                        easing: 'easeOutQuart'
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#ffffff',
                            titleColor: '#0f172a',
                            bodyColor: '#475569',
                            padding: 14,
                            cornerRadius: 10,
                            borderColor: 'rgba(16, 185, 129, 0.2)',
                            borderWidth: 1,
                            titleFont: { family: 'Plus Jakarta Sans', size: 13, weight: 'bold' },
                            bodyFont: { family: 'Plus Jakarta Sans', size: 13 },
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + '% Terealisasi';
                                }
                            },
                            // Efek bayangan pada tooltip
                            footerMarginTop: 10,
                            boxShadow: '0 4px 12px rgba(0,0,0,0.1)'
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false, drawBorder: false },
                            ticks: { color: '#64748b', font: { family: 'Plus Jakarta Sans', weight: 'bold' } }
                        },
                        y: {
                            grid: { color: 'rgba(0,0,0,0.05)', borderDash: [5, 5], drawBorder: false },
                            ticks: {
                                color: '#64748b',
                                font: { family: 'Plus Jakarta Sans', weight: 'bold' },
                                callback: function(value) { return value + '%'; }
                            },
                            min: 0,
                            max: 100
                        }
                    }
                }
            });
        });
    </script>
@endpush