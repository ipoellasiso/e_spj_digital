@extends('layouts.app')
@section('title', 'Pendapatan')
@section('nav', 'superadmin-pendapatan')

@section('content')
<div class="container py-4">
    <h1 class="page-title mb-4">Dashboard Pendapatan</h1>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card-tabel p-4 bg-success text-white">
                <h6 class="fw-bold">Total Pendapatan</h6>
                <h2 class="fw-bold mb-0">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-tabel p-4">
                <h6 class="text-secondary fw-bold">Bulan Ini</h6>
                <h2 class="fw-bold mb-0 text-dark">Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }}</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-tabel p-4">
                <h6 class="text-secondary fw-bold">Tahun Ini</h6>
                <h2 class="fw-bold mb-0 text-dark">Rp {{ number_format($pendapatanTahunIni, 0, ',', '.') }}</h2>
            </div>
        </div>
    </div>

    <div class="card-tabel p-4">
        <h5 class="fw-bold mb-4">Tren Pendapatan 12 Bulan Terakhir</h5>
        <canvas id="chartPendapatan" height="100"></canvas>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var ctx = document.getElementById('chartPendapatan').getContext('2d');
    var gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(16, 185, 129, 0.4)');
    gradient.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Bln -11', 'Bln -10', 'Bln -9', 'Bln -8', 'Bln -7', 'Bln -6', 'Bln -5', 'Bln -4', 'Bln -3', 'Bln -2', 'Bln -1', 'Bulan Ini'],
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: @json($chartData),
                borderColor: '#10b981',
                backgroundColor: gradient,
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                }
            }
        }
    });
</script>
@endpush