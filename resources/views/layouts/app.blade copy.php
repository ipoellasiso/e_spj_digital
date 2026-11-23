<!doctype html>
{{-- Layout Bootstrap 5.3. Tema hijau-kuning lewat variabel CSS di bawah.
     Produksi: pasang lokal (npm i bootstrap bootstrap-icons) dan hapus link CDN. --}}
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') - {{ config('app.name', 'e-SPJ') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <link href="{{ asset('css/tema-spj.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
@php($nav = trim($__env->yieldContent('nav')))
<div class="app-shell">

    {{-- Sidebar: panel geser di layar kecil, tetap di layar lebar --}}
    <aside class="offcanvas-lg offcanvas-start sidebar" tabindex="-1" id="sidebar" aria-label="Menu utama">
        <div class="offcanvas-header d-lg-none justify-content-end pb-0">
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Tutup menu"></button>
        </div>
        <a class="brand" href="{{ route('dashboard') }}">
            <span class="brand-mark"><i class="bi bi-receipt-cutoff"></i></span>
            <span><strong>e-SPJ</strong><small>Pertanggungjawaban belanja</small></span>
        </a>

        <nav class="pb-4">
            <a class="nav-tautan {{ $nav === 'beranda' ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-house-door"></i> Beranda</a>

            <div class="nav-grup">Anggaran</div>
            <a class="nav-tautan {{ $nav === 'dpa' ? 'active' : '' }}" href="{{ route('dpa.index') }}"><i class="bi bi-journal-text"></i> DPA dan sisa pagu</a>
            @hasanyrole('superadmin|admin|bendahara')
                <a class="nav-tautan {{ $nav === 'impor-dpa' ? 'active' : '' }}" href="{{ route('dpa.impor') }}"><i class="bi bi-file-earmark-arrow-up"></i> Impor DPA dari Excel</a>
            @endhasanyrole

            <div class="nav-grup">SPJ</div>
            <a class="nav-tautan {{ $nav === 'spj' ? 'active' : '' }}" href="#"><i class="bi bi-files"></i> Daftar SPJ</a>
            @hasanyrole('bendahara|pptk')
                <a class="nav-tautan {{ $nav === 'spj-baru' ? 'active' : '' }}" href="#"><i class="bi bi-plus-circle"></i> Buat SPJ</a>
            @endhasanyrole
            @hasanyrole('bendahara|ppk_skpd|pa')
                {{-- angka 3 masih contoh; ganti dengan jumlah SPJ yang menunggu tindakan user --}}
                <a class="nav-tautan {{ $nav === 'verifikasi' ? 'active' : '' }}" href="#"><i class="bi bi-patch-check"></i> Verifikasi dan persetujuan <span class="jumlah">3</span></a>
            @endhasanyrole

            <div class="nav-grup">Laporan</div>
            <a class="nav-tautan {{ $nav === 'bku' ? 'active' : '' }}" href="#"><i class="bi bi-book"></i> Buku kas umum</a>
            <a class="nav-tautan {{ $nav === 'realisasi' ? 'active' : '' }}" href="#"><i class="bi bi-bar-chart-line"></i> Realisasi anggaran</a>
            <a class="nav-tautan {{ $nav === 'pajak' ? 'active' : '' }}" href="#"><i class="bi bi-percent"></i> Rekap pajak</a>

            @hasanyrole('superadmin|admin|bendahara')
                <div class="nav-grup">Data master</div>
                @role('superadmin')
                    <a class="nav-tautan {{ $nav === 'opd' ? 'active' : '' }}" href="{{ route('opd.index') }}"><i class="bi bi-building"></i> OPD</a>
                    <a class="nav-tautan {{ $nav === 'sumber-dana' ? 'active' : '' }}" href="#"><i class="bi bi-cash-coin"></i> Sumber dana</a>
                @endrole
                @hasanyrole('superadmin|admin')
                    <a class="nav-tautan {{ $nav === 'pejabat' ? 'active' : '' }}" href="{{ route('pejabat.index') }}"><i class="bi bi-person-vcard"></i> Pejabat penandatangan</a>
                    <a class="nav-tautan {{ $nav === 'sk' ? 'active' : '' }}" href="{{ route('sk.index') }}"><i class="bi bi-file-earmark-text"></i> SK penunjukan</a>
                @endhasanyrole
                <a class="nav-tautan {{ $nav === 'penyedia' ? 'active' : '' }}" href="{{ route('penyedia.index') }}"><i class="bi bi-shop"></i> Penyedia</a>
                <a class="nav-tautan {{ $nav === 'pptk' ? 'active' : '' }}" href="{{ route('pptk.index') }}"><i class="bi bi-diagram-3"></i> Penugasan PPTK</a>

                <div class="nav-grup">Pengaturan</div>
                @hasanyrole('superadmin|admin')
                    <a class="nav-tautan {{ $nav === 'pengguna' ? 'active' : '' }}" href="{{ route('pengguna.index') }}"><i class="bi bi-people"></i> Pengguna</a>
                @endhasanyrole
                <a class="nav-tautan {{ $nav === 'periode' ? 'active' : '' }}" href="#"><i class="bi bi-lock"></i> Kunci periode</a>
                @hasanyrole('superadmin|admin')
                    <a class="nav-tautan {{ $nav === 'log' ? 'active' : '' }}" href="#"><i class="bi bi-clock-history"></i> Log aktivitas</a>
                @endhasanyrole
            @endhasanyrole
        </nav>
    </aside>

    <div class="app-main">
        <header class="topbar">
            <button class="btn btn-light d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Buka menu">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div class="konteks">
                <span class="chip"><b>{{ $ctx->opd }}</b></span>
                <span class="chip">Tahun anggaran <b>{{ $ctx->tahun }}</b></span>
                <span class="chip d-none d-sm-inline"><b>{{ $ctx->dpa }}</b></span>
            </div>
            <div class="dropdown ms-auto">
                <button class="btn d-flex align-items-center gap-2 p-1" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="avatar">{{ $ctx->inisial }}</span>
                    <span class="text-start d-none d-md-block lh-sm">
                        <span class="d-block fw-semibold">{{ auth()->user()->name }}</span>
                        <small class="text-secondary">{{ $ctx->peran }}</small>
                    </span>
                    <i class="bi bi-chevron-down small text-secondary"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#">Profil saya</a></li>
                    <li><a class="dropdown-item" href="#">Ganti kata sandi</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button class="dropdown-item" type="submit">Keluar</button></form>
                    </li>
                </ul>
            </div>
        </header>

        <main class="konten">
            @if (session('sukses'))
                <div class="alert alert-success d-flex align-items-center gap-2" role="status"><i class="bi bi-check-circle"></i> {{ session('sukses') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>