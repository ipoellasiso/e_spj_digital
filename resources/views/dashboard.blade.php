@extends('layouts.app')

@section('title', 'Beranda')
@section('nav', 'beranda')

@push('styles')
<style>
    /* Animasi Fade-In Up */
    .animate-up {
        animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        opacity: 0;
        transform: translateY(20px);
    }
    @keyframes slideUp { to { opacity: 1; transform: translateY(0); } }

    /* Efek Hover Kartu */
    .hover-lift {
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease;
    }
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.08) !important;
    }

    /* Progress bar animasi */
    .progress-animated {
        width: 0;
        transition: width 1.5s cubic-bezier(0.16, 1, 0.3, 1);
    }
</style>
@endpush

@section('content')
    {{-- Header Halaman --}}
    <div class="page-header animate-up" style="animation-delay: 0.1s;">
        <div>
            <h1 class="page-title">Beranda</h1>
            <p class="page-desc">Posisi anggaran per {{ now()->translatedFormat('j F Y') }}</p>
        </div>
        <div>
            <a href="#" class="btn-premium">
                <i class="bi bi-plus-lg"></i> Buat SPJ
            </a>
        </div>
    </div>

    {{-- Baris 1: Kartu KPI --}}
    <div class="row g-4 mb-4">
        {{-- KPI 1: Pagu --}}
        <div class="col-md-4 animate-up" style="animation-delay: 0.2s;">
            <div class="card-kpi hover-lift">
                <div class="kpi-icon" style="background: rgba(99, 102, 241, 0.1); color: #6366F1;">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="kpi-content">
                    <span class="kpi-label">Pagu Anggaran</span>
                    <h3 class="kpi-value angka counter" data-target="{{ $pagu }}">Rp0</h3>
                </div>
            </div>
        </div>
        
        {{-- KPI 2: Realisasi --}}
        <div class="col-md-4 animate-up" style="animation-delay: 0.3s;">
            <div class="card-kpi hover-lift">
                <div class="kpi-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--hijau-600);">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="kpi-content">
                    <span class="kpi-label">Realisasi Final</span>
                    <h3 class="kpi-value angka counter" data-target="{{ $realisasi }}">Rp0</h3>
                    <span class="kpi-trend text-success"><i class="bi bi-arrow-up-right"></i> {{ $persen_final }}% dari pagu</span>
                </div>
            </div>
        </div>

        {{-- KPI 3: Proses --}}
        <div class="col-md-4 animate-up" style="animation-delay: 0.4s;">
            <div class="card-kpi hover-lift">
                <div class="kpi-icon" style="background: rgba(251, 191, 36, 0.1); color: var(--kuning-500);">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="kpi-content">
                    <span class="kpi-label">SPJ Dalam Proses</span>
                    <h3 class="kpi-value angka counter" data-target="{{ $proses }}">Rp0</h3>
                    <span class="kpi-trend text-warning"><i class="bi bi-clock"></i> {{ $persen_proses }}% dari pagu</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Baris 2: Chart & Progress Sisa Pagu --}}
    <div class="row g-4 mb-4">
        {{-- Chart --}}
        <div class="col-lg-8 animate-up" style="animation-delay: 0.5s;">
            <div class="card-modern h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Tren Realisasi Anggaran</span>
                    <select class="form-select form-select-sm" style="width: auto;">
                        <option>Tahun {{ now()->year }}</option>
                    </select>
                </div>
                <div class="card-body p-4">
                    <canvas id="chartRealisasi" height="100"></canvas>
                </div>
            </div>
        </div>

        {{-- Progress Bar Sisa Pagu --}}
        <div class="col-lg-4 animate-up" style="animation-delay: 0.6s;">
            <div class="card-modern h-100">
                <div class="card-header">
                    <span>Pagu Paling Tipis</span>
                </div>
                <div class="card-body p-4">
                    @foreach ([['Belanja bahan bakar', 8], ['Belanja alat tulis kantor', 14], ['Belanja jasa kebersihan', 22], ['Belanja pemeliharaan', 35]] as [$nama, $sisa_persen])
                        <div class="mb-4">
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="fw-semibold text-dark">{{ $nama }}</span>
                                <span class="angka text-secondary">sisa {{ $sisa_persen }}%</span>
                            </div>
                            <div class="progress-modern">
                                <div class="progress-bar progress-animated" style="--target-width: {{ 100 - $sisa_persen }}%; background:{{ $sisa_persen <= 10 ? '#ef4444' : 'var(--hijau-600)' }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Baris 3: Tabel SPJ & Antrian Verifikasi --}}
    <div class="row g-4">
        {{-- SPJ Terbaru --}}
        <div class="col-xl-7 animate-up" style="animation-delay: 0.7s;">
            <div class="card-modern h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>SPJ Terbaru</span>
                    <a href="#" class="text-decoration-none small fw-bold" style="color: var(--hijau-600);">Lihat Semua <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="table-responsive">
                    <table class="tabel tabel-modern w-100">
                        <thead>
                            <tr>
                                <th>Nomor</th>
                                <th>Uraian</th>
                                <th class="text-end">Jumlah</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody class="angka">
                            @foreach($spj_terbaru as $spj)
                            <tr>
                                <td class="fw-semibold">{{ $spj['no'] }}</td>
                                <td>{{ $spj['uraian'] }}@if($spj['kode'])<div class="small text-secondary">{{ $spj['kode'] }}</div>@endif</td>
                                <td class="text-end">Rp{{ number_format($spj['jumlah'], 0, ',', '.') }}</td>
                                <td>
                                    @if($spj['status'] == 'Final')
                                        <span class="st st-verif" style="background: var(--hijau-500); color: #fff;">Final</span>
                                    @elseif($spj['status'] == 'Diajukan')
                                        <span class="st st-diajukan" style="background: #e0e7ff; color: #4338ca;">Diajukan</span>
                                    @else
                                        <span class="st st-verif" style="background: rgba(251,191,36,0.2); color: #b45309;">Verifikasi</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Antrian Tindakan --}}
        <div class="col-xl-5 animate-up" style="animation-delay: 0.8s;">
            <div class="card-modern h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Menunggu Verifikasi Anda</span>
                    <span class="badge bg-danger rounded-pill">3 SPJ</span>
                </div>
                <ul class="list-unstyled m-0">
                    @foreach ([['0264', 'Belanja alat tulis kantor', 'Rp2.340.000', 'Diajukan 20 Nov'], ['0267', 'Jasa kebersihan kantor', 'Rp3.000.000', 'Diajukan 20 Nov'], ['0268', 'Perawatan AC ruang rapat', 'Rp1.850.000', 'Diajukan 19 Nov']] as [$no, $uraian, $jumlah, $kapan])
                        <li class="d-flex align-items-center gap-3 px-4 py-3 border-bottom border-light hover-lift">
                            <div class="flex-grow-1">
                                <div class="fw-semibold text-dark">{{ $uraian }}</div>
                                <div class="small text-secondary angka">No. {{ $no }} &middot; {{ $jumlah }} &middot; {{ $kapan }}</div>
                            </div>
                            <a href="#" class="btn-aksi-ikon text-success" title="Verifikasi"><i class="bi bi-check2-square fs-5"></i></a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // --- 1. ANIMASI COUNTER ANGKA ---
            const counters = document.querySelectorAll('.counter');
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-target');
                if(target === 0) return; // Skip jika 0

                const duration = 1500;
                const increment = target / (duration / 16); 
                let current = 0;
                
                const updateCounter = () => {
                    current += increment;
                    if (current < target) {
                        counter.innerText = 'Rp' + new Intl.NumberFormat('id-ID').format(Math.ceil(current));
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.innerText = 'Rp' + new Intl.NumberFormat('id-ID').format(target);
                    }
                };
                setTimeout(updateCounter, 300); 
            });

            // --- 2. ANIMASI PROGRESS BAR ---
            const progressBars = document.querySelectorAll('.progress-animated');
            setTimeout(() => {
                progressBars.forEach(bar => {
                    const targetWidth = bar.style.getPropertyValue('--target-width');
                    bar.style.width = targetWidth;
                });
            }, 500);

            // --- 3. ANIMASI CHART.JS ---
            var ctx = document.getElementById('chartRealisasi').getContext('2d');
            var gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(16, 185, 129, 0.4)');
            gradient.addColorStop(1, 'rgba(16, 185, 129, 0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    datasets: [{
                        label: 'Realisasi',
                        data: @json($chart_data),
                        borderColor: '#10b981',
                        backgroundColor: gradient,
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#10b981',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 2000, // Animasi gambar chart 2 detik
                        easing: 'easeOutQuart'
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 12,
                            titleFont: { family: 'Plus Jakarta Sans', size: 13 },
                            bodyFont: { family: 'Plus Jakarta Sans', size: 13 },
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + '% Terealisasi';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false, drawBorder: false },
                            ticks: { color: '#64748b', font: { family: 'Plus Jakarta Sans' } }
                        },
                        y: {
                            grid: { color: 'rgba(0,0,0,0.05)', borderDash: [5, 5], drawBorder: false },
                            ticks: {
                                color: '#64748b',
                                font: { family: 'Plus Jakarta Sans' },
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