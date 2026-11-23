<!doctype html>
<html lang="id" data-tema="terang">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') - {{ config('app.name', 'e-SPJ') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/tema-spj.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
@php($nav = trim($__env->yieldContent('nav')))
<div class="app-shell" id="appShell">

    {{-- SIDEBAR --}}
    <aside class="offcanvas-lg offcanvas-start sidebar" tabindex="-1" id="sidebar" aria-label="Menu utama">
        <div class="offcanvas-header d-lg-none justify-content-end pb-0">
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Tutup menu"></button>
        </div>

        <a class="brand" href="{{ route('dashboard') }}">
            <span class="brand-mark"><i class="bi bi-receipt-cutoff"></i></span>
            <span class="brand-text">
                <strong>e-SPJ</strong>
                <small>Pertanggungjawaban belanja</small>
            </span>
        </a>

        <nav class="sidebar-nav pb-4">
            <a class="nav-tautan {{ $nav === 'beranda' ? 'active' : '' }}" href="{{ route('dashboard') }}" data-tooltip="Beranda">
                <i class="bi bi-house-door"></i><span>Beranda</span>
            </a>

            <div class="nav-grup">Anggaran</div>
            <a class="nav-tautan {{ $nav === 'dpa' ? 'active' : '' }}" href="{{ route('dpa.index') }}" data-tooltip="DPA dan sisa pagu">
                <i class="bi bi-journal-text"></i><span>DPA dan sisa pagu</span>
            </a>
            @hasanyrole('superadmin|admin|bendahara')
                <a class="nav-tautan {{ $nav === 'impor-dpa' ? 'active' : '' }}" href="{{ route('dpa.impor') }}" data-tooltip="Impor DPA dari Excel">
                    <i class="bi bi-file-earmark-arrow-up"></i><span>Impor DPA dari Excel</span>
                </a>
            @endhasanyrole

            <div class="nav-grup">SPJ</div>
            <a class="nav-tautan {{ $nav === 'spj' ? 'active' : '' }}" href="{{ route('spj.index') }}" data-tooltip="Daftar SPJ">
                <i class="bi bi-files"></i><span>Daftar SPJ</span>
            </a>
            @hasanyrole('bendahara|pptk')
                <a class="nav-tautan {{ $nav === 'spj-baru' ? 'active' : '' }}" href="{{ route('spj.create') }}" data-tooltip="Buat SPJ">
                    <i class="bi bi-plus-circle"></i><span>Buat SPJ</span>
                </a>
            @endhasanyrole
            
            @hasanyrole('bendahara|ppk_skpd|pa')
                <a class="nav-tautan {{ $nav === 'verifikasi' ? 'active' : '' }}" href="{{ route('spj.antrian') }}">
                    <i class="bi bi-patch-check"></i><span>Verifikasi dan persetujuan</span>
                    @if (($ctx->menunggu ?? 0) > 0)
                        <span class="jumlah">{{ $ctx->menunggu }}</span>
                    @endif
                </a>
            @endhasanyrole

            <div class="nav-grup">Laporan</div>
            <a class="nav-tautan {{ $nav === 'bku' ? 'active' : '' }}" href="#" data-tooltip="Buku kas umum"><i class="bi bi-book"></i><span>Buku kas umum</span></a>
            <a class="nav-tautan {{ $nav === 'realisasi' ? 'active' : '' }}" href="#" data-tooltip="Realisasi anggaran"><i class="bi bi-bar-chart-line"></i><span>Realisasi anggaran</span></a>
            <a class="nav-tautan {{ $nav === 'pajak' ? 'active' : '' }}" href="#" data-tooltip="Rekap pajak"><i class="bi bi-percent"></i><span>Rekap pajak</span></a>

            @hasanyrole('superadmin|admin|bendahara')
                <div class="nav-grup">Data master</div>
                @role('superadmin')
                    <a class="nav-tautan {{ $nav === 'opd' ? 'active' : '' }}" href="{{ route('opd.index') }}" data-tooltip="OPD"><i class="bi bi-building"></i><span>OPD</span></a>
                    <a class="nav-tautan {{ $nav === 'sumber-dana' ? 'active' : '' }}" href="#" data-tooltip="Sumber dana"><i class="bi bi-cash-coin"></i><span>Sumber dana</span></a>
                @endrole
                @hasanyrole('superadmin|admin')
                    <a class="nav-tautan {{ $nav === 'pejabat' ? 'active' : '' }}" href="{{ route('pejabat.index') }}" data-tooltip="Pejabat penandatangan"><i class="bi bi-person-vcard"></i><span>Pejabat penandatangan</span></a>
                    <a class="nav-tautan {{ $nav === 'sk' ? 'active' : '' }}" href="{{ route('sk.index') }}" data-tooltip="SK penunjukan"><i class="bi bi-file-earmark-text"></i><span>SK penunjukan</span></a>
                @endhasanyrole
                <a class="nav-tautan {{ $nav === 'penyedia' ? 'active' : '' }}" href="{{ route('penyedia.index') }}" data-tooltip="Penyedia"><i class="bi bi-shop"></i><span>Penyedia</span></a>
                <a class="nav-tautan {{ $nav === 'pptk' ? 'active' : '' }}" href="{{ route('pptk.index') }}" data-tooltip="Penugasan PPTK"><i class="bi bi-diagram-3"></i><span>Penugasan PPTK</span></a>

                <div class="nav-grup">Pengaturan</div>
                @hasanyrole('superadmin|admin')
                    <a class="nav-tautan {{ $nav === 'pengguna' ? 'active' : '' }}" href="{{ route('pengguna.index') }}" data-tooltip="Pengguna"><i class="bi bi-people"></i><span>Pengguna</span></a>
                @endhasanyrole
                <a class="nav-tautan {{ $nav === 'periode' ? 'active' : '' }}" href="#" data-tooltip="Kunci periode"><i class="bi bi-lock"></i><span>Kunci periode</span></a>
                @hasanyrole('superadmin|admin')
                    <a class="nav-tautan {{ $nav === 'log' ? 'active' : '' }}" href="#" data-tooltip="Log aktivitas"><i class="bi bi-clock-history"></i><span>Log aktivitas</span></a>
                @endhasanyrole
            @endhasanyrole
        </nav>

        <div class="sidebar-footer">
            <div class="bantuan-card">
                <div class="bantuan-icon"><i class="bi bi-lightbulb"></i></div>
                <div>
                    <strong>Butuh bantuan?</strong>
                    <small>Panduan penggunaan e-SPJ</small>
                </div>
                <a href="#" class="btn-bantuan">Buka <i class="bi bi-arrow-right-short"></i></a>
            </div>
        </div>
    </aside>

    <div class="app-main">
        <header class="topbar">
            {{-- Tombol Collapse Sidebar (Desktop Only) --}}
            <button class="btn btn-ikon d-none d-lg-grid" type="button" id="tombolCollapse" title="Sembunyikan menu" aria-label="Sembunyikan menu">
                <i class="bi bi-layout-sidebar-inset"></i>
            </button>

            {{-- Tombol Hamburger Mobile --}}
            <button class="btn btn-ikon d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Buka menu">
                <i class="bi bi-list fs-5"></i>
            </button>

            <div class="konteks">
                <span class="chip"><i class="bi bi-building"></i><b>{{ $ctx->opd }}</b></span>
                <span class="chip"><i class="bi bi-calendar3"></i>Tahun <b>{{ $ctx->tahun }}</b></span>
                <span class="chip d-none d-sm-inline"><i class="bi bi-journal-bookmark"></i><b>{{ $ctx->dpa }}</b></span>
            </div>

            <div class="aksi-topbar ms-auto">
                {{-- Toggle tema --}}
                <button class="btn btn-ikon" type="button" id="tombolTema" title="Ganti tema">
                    <i class="bi bi-moon-stars"></i>
                </button>

                {{-- Notifikasi --}}
                <button class="btn btn-ikon posisi-relatif" type="button" title="Notifikasi">
                    <i class="bi bi-bell"></i>
                    <span class="notif-dot"></span>
                </button>

                {{-- Profil --}}
                <div class="dropdown">
                    <button class="profil-tombol" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar">{{ $ctx->inisial }}</span>
                        <span class="text-start d-none d-md-block lh-sm">
                            <span class="d-block fw-semibold">{{ auth()->user()->name }}</span>
                            <small class="text-secondary">{{ $ctx->peran }}</small>
                        </span>
                        <i class="bi bi-chevron-down small text-secondary"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-profil">
                        <li class="dropdown-header-profil">
                            <span class="avatar avatar-lg">{{ $ctx->inisial }}</span>
                            <div>
                                <strong>{{ auth()->user()->name }}</strong>
                                <small>{{ $ctx->peran }}</small>
                            </div>
                        </li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person"></i> Profil saya</a></li>
                        <li><a class="dropdown-item" href="{{ route('profil.password') }}"><i class="bi bi-key"></i> Ganti kata sandi</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item text-danger" type="submit">
                                    <i class="bi bi-box-arrow-right"></i> Keluar
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="konten">
            @yield('content')
        </main>
    </div>
</div>

{{-- TOAST NOTIFICATION CONTAINER --}}
<div class="toast-container position-fixed top-0 end-0 p-4 mt-4" style="z-index: 9999;">
    @if (session('sukses'))
        <div id="toastSukses" class="toast-custom" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000" data-bs-autohide="true">
            <div class="toast-icon">
                <i class="bi bi-check-lg"></i>
            </div>
            <div class="toast-content">
                <span class="toast-title">Berhasil!</span>
                <span class="toast-message">{{ session('sukses') }}</span>
            </div>
            <button type="button" class="btn-close-toast" data-bs-dismiss="toast" aria-label="Close">
                <i class="bi bi-x"></i>
            </button>
            <div class="toast-progress"></div>
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // ---------- Toggle Tema ----------
    (function () {
        const html = document.documentElement;
        const tombol = document.getElementById('tombolTema');
        const simpan = localStorage.getItem('tema-spj');
        if (simpan) html.dataset.tema = simpan;
        if (tombol) {
            if (html.dataset.tema === 'gelap') tombol.querySelector('i').className = 'bi bi-sun';
            tombol.addEventListener('click', () => {
                const baru = html.dataset.tema === 'gelap' ? 'terang' : 'gelap';
                html.dataset.tema = baru;
                localStorage.setItem('tema-spj', baru);
                tombol.querySelector('i').className = baru === 'gelap' ? 'bi bi-sun' : 'bi bi-moon-stars';
            });
        }
    })();

    // ---------- Collapse Sidebar ----------
    (function () {
        const shell = document.getElementById('appShell');
        const tombol = document.getElementById('tombolCollapse');
        if (!shell || !tombol) return;

        const icon = tombol.querySelector('i');
        const tersimpan = localStorage.getItem('sidebar-mini');

        // Restore state
        if (tersimpan === 'true') {
            shell.classList.add('sidebar-mini');
            icon.className = 'bi bi-layout-sidebar';
            tombol.setAttribute('title', 'Tampilkan menu');
        }

        // Aksi klik
        tombol.addEventListener('click', () => {
            shell.classList.toggle('sidebar-mini');
            const isMini = shell.classList.contains('sidebar-mini');
            localStorage.setItem('sidebar-mini', isMini);

            if (isMini) {
                icon.className = 'bi bi-layout-sidebar';
                tombol.setAttribute('title', 'Tampilkan menu');
            } else {
                icon.className = 'bi bi-layout-sidebar-inset';
                tombol.setAttribute('title', 'Sembunyikan menu');
            }
        });
    })();

    // Pemicu Toast Sukses
    document.addEventListener("DOMContentLoaded", function() {
        var toastEl = document.getElementById('toastSukses');
        if (toastEl) {
            // Munculkan Toast
            var toast = new bootstrap.Toast(toastEl);
            toast.show();
            
            // Hapus Toast dari DOM setelah selesai animasi
            toastEl.addEventListener('hidden.bs.toast', function () {
                toastEl.remove();
            });
        }
    });
</script>

@stack('scripts')
</body>
</html>