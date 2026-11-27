@extends('layouts.app')
@section('title', 'Panduan Penggunaan')
@section('nav', 'panduan')

@section('content')
<div class="container py-4" style="max-width: 800px;">
    <div class="card-tabel p-5">
        <h1 class="fw-bold mb-4 text-success">Panduan Penggunaan e-SPJ</h1>
        
        <div class="mb-4">
            <h4 class="fw-bold">1. Cara Membuat SPJ</h4>
            <p>Masuk ke menu <strong>Daftar SPJ -> Buat SPJ</strong>. Isi form yang disediakan, lalu klik Simpan.</p>
        </div>

        <div class="mb-4">
            <h4 class="fw-bold">2. Impor DPA dari Excel</h4>
            <p>Masuk ke menu <strong>Impor DPA</strong>. Pastikan file Excel mengikuti format yang sudah ditentukan.</p>
        </div>

        <div class="mb-4">
            <h4 class="fw-bold">3. Cara Upload Bukti Pembayaran</h4>
            <p>Buka menu <strong>Langganan & Tagihan</strong>, lalu unggah foto bukti transfer pada tagihan yang masih berstatus <em>Menunggu Pembayaran</em>.</p>
        </div>

        <div class="alert alert-info mt-5">
            <i class="bi bi-info-circle me-2"></i> Jika Anda mengalami kesulitan, silakan hubungi Admin OPD atau Superadmin.
        </div>
    </div>
</div>
@endsection