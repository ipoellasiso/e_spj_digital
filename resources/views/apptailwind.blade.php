<!doctype html>
{{-- Layout Tailwind 3. Tema hijau-kuning didefinisikan di tailwind.config di bawah.
     Produksi: pindahkan config ke tailwind.config.js + Vite, hapus script CDN dan blok <style type="text/tailwindcss">
     (isinya pindah ke resources/css/app.css). --}}
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') - {{ config('app.name', 'e-SPJ') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        hutan:  { DEFAULT: '#123d27' },                                   // sidebar, judul
                        daun:   { DEFAULT: '#1f7a3f', tua: '#176334', muda: '#e4f1e7' },  // tombol, realisasi final
                        kuning: { DEFAULT: '#f5c518', muda: '#fff3bf', tua: '#6b5200' },  // penanda aktif, SPJ dalam proses
                        kertas: '#f3f6f1', tinta: '#14231a', redam: '#5b6b60', garis: '#dce4da', bata: '#b4412f',
                    },
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'system-ui', '-apple-system', '"Segoe UI"', 'sans-serif'] },
                },
            },
        }
    </script>
    <style type="text/tailwindcss">
        @layer base {
            body { @apply bg-kertas font-sans text-[.94rem] text-tinta antialiased; }
            :focus-visible { @apply outline outline-[3px] outline-offset-2 outline-kuning; }
        }
        @layer components {
            .angka { font-variant-numeric: tabular-nums; }
            .nav-grup { @apply px-5 pb-1 pt-5 text-[.78rem] font-semibold text-[#8fb59b]; }
            .nav-tautan { @apply flex items-center gap-3 border-l-4 border-transparent py-2 pl-[1.1rem] pr-5 text-[#cfe3d4] hover:bg-white/5 hover:text-white; }
            .nav-tautan.is-active { @apply border-kuning bg-white/10 font-semibold text-white; }
            .panel { @apply rounded-xl border border-garis bg-white; }
            .panel-kepala { @apply flex items-baseline justify-between gap-4 border-b border-garis px-5 py-4; }
            .panel-kepala h2 { @apply text-base font-bold text-hutan; }
            .th { @apply whitespace-nowrap border-b border-garis px-4 py-3 text-left text-[.82rem] font-semibold text-redam; }
            .td { @apply border-b border-[#edf1eb] px-4 py-3 align-middle; }
            .btn-kuning { @apply inline-flex items-center gap-1.5 rounded-lg bg-kuning px-4 py-2 font-semibold text-hutan hover:bg-[#e5b70d]; }
            .btn-garis  { @apply inline-flex items-center rounded-lg border border-daun px-3 py-1.5 text-sm font-medium text-daun-tua hover:bg-daun hover:text-white; }
            .st { @apply inline-block whitespace-nowrap rounded-md px-2.5 py-0.5 text-[.78rem] font-semibold; }
            .st-draft { @apply bg-[#eceeea] text-[#4b564e]; }
            .st-diajukan { @apply bg-kuning-muda text-kuning-tua; }
            .st-verif { @apply bg-daun-muda text-daun-tua; }
            .st-final { @apply bg-daun text-white; }
            .st-kembali { @apply bg-[#f8e2dd] text-bata; }
            .st-batal { @apply bg-[#eceeea] text-[#6b746d] line-through; }
        }
    </style>
    @stack('styles')
</head>
<body>
@php($nav = trim($__env->yieldContent('nav')))

{{-- Lapisan gelap saat menu terbuka di layar kecil --}}
<div id="overlay" class="fixed inset-0 z-30 hidden bg-black/50 lg:hidden"></div>

<aside id="sidebar" aria-label="Menu utama"
       class="fixed inset-y-0 left-0 z-40 w-[264px] -translate-x-full overflow-y-auto bg-hutan text-[#cfe3d4] transition-transform motion-reduce:transition-none lg:translate-x-0">
    <a href="#" class="flex items-center gap-3 px-5 pb-4 pt-5 text-white">
        <span class="grid h-10 w-10 flex-none place-items-center rounded-[10px] bg-kuning text-xl text-hutan"><i class="bi bi-receipt-cutoff"></i></span>
        <span class="leading-tight"><strong class="block">e-SPJ</strong><small class="text-[.76rem] text-[#9ec2a8]">Pertanggungjawaban belanja</small></span>
    </a>

    <nav class="pb-6">
        <a class="nav-tautan {{ $nav === 'beranda' ? 'is-active' : '' }}" href="#"><i class="bi bi-house-door w-5 text-center"></i> Beranda</a>

        <div class="nav-grup">Anggaran</div>
        <a class="nav-tautan {{ $nav === 'dpa' ? 'is-active' : '' }}" href="#"><i class="bi bi-journal-text w-5 text-center"></i> DPA dan sisa pagu</a>
        <a class="nav-tautan {{ $nav === 'impor-dpa' ? 'is-active' : '' }}" href="#"><i class="bi bi-file-earmark-arrow-up w-5 text-center"></i> Impor DPA dari Excel</a>

        <div class="nav-grup">SPJ</div>
        <a class="nav-tautan {{ $nav === 'spj' ? 'is-active' : '' }}" href="#"><i class="bi bi-files w-5 text-center"></i> Daftar SPJ</a>
        <a class="nav-tautan {{ $nav === 'spj-baru' ? 'is-active' : '' }}" href="#"><i class="bi bi-plus-circle w-5 text-center"></i> Buat SPJ</a>
        <a class="nav-tautan {{ $nav === 'verifikasi' ? 'is-active' : '' }}" href="#">
            <i class="bi bi-patch-check w-5 text-center"></i> Verifikasi dan persetujuan
            <span class="ml-auto rounded-full bg-kuning px-2 text-[.76rem] font-bold text-hutan">3</span>
        </a>

        <div class="nav-grup">Laporan</div>
        <a class="nav-tautan {{ $nav === 'bku' ? 'is-active' : '' }}" href="#"><i class="bi bi-book w-5 text-center"></i> Buku kas umum</a>
        <a class="nav-tautan {{ $nav === 'realisasi' ? 'is-active' : '' }}" href="#"><i class="bi bi-bar-chart-line w-5 text-center"></i> Realisasi anggaran</a>
        <a class="nav-tautan {{ $nav === 'pajak' ? 'is-active' : '' }}" href="#"><i class="bi bi-percent w-5 text-center"></i> Rekap pajak</a>

        <div class="nav-grup">Data master</div>
        <a class="nav-tautan {{ $nav === 'pejabat' ? 'is-active' : '' }}" href="#"><i class="bi bi-person-vcard w-5 text-center"></i> Pejabat penandatangan</a>
        <a class="nav-tautan {{ $nav === 'sk' ? 'is-active' : '' }}" href="#"><i class="bi bi-file-earmark-text w-5 text-center"></i> SK penunjukan</a>
        <a class="nav-tautan {{ $nav === 'penyedia' ? 'is-active' : '' }}" href="#"><i class="bi bi-shop w-5 text-center"></i> Penyedia</a>

        <div class="nav-grup">Pengaturan</div>
        <a class="nav-tautan {{ $nav === 'pengguna' ? 'is-active' : '' }}" href="#"><i class="bi bi-people w-5 text-center"></i> Pengguna</a>
        <a class="nav-tautan {{ $nav === 'periode' ? 'is-active' : '' }}" href="#"><i class="bi bi-lock w-5 text-center"></i> Kunci periode</a>
        <a class="nav-tautan {{ $nav === 'log' ? 'is-active' : '' }}" href="#"><i class="bi bi-clock-history w-5 text-center"></i> Log aktivitas</a>
    </nav>
</aside>

<div class="flex min-h-screen flex-col lg:pl-[264px]">
    <header class="sticky top-0 z-20 flex items-center gap-4 border-b border-garis bg-white px-5 py-2.5 lg:px-8">
        <button id="buka-menu" type="button" class="rounded-lg p-2 text-xl hover:bg-kertas lg:hidden" aria-controls="sidebar" aria-expanded="false" aria-label="Buka menu">
            <i class="bi bi-list"></i>
        </button>

        {{-- ganti dengan data asli: auth()->user()->opd->nama, tahun aktif, versi DPA aktif --}}
        <div class="flex flex-wrap gap-2 text-[.82rem] text-redam">
            <span class="rounded-full border border-garis bg-kertas px-3 py-0.5"><b class="font-semibold text-tinta">Kecamatan Tawaeli</b></span>
            <span class="rounded-full border border-garis bg-kertas px-3 py-0.5">Tahun anggaran <b class="font-semibold text-tinta">2026</b></span>
            <span class="hidden rounded-full border border-garis bg-kertas px-3 py-0.5 sm:inline">DPA <b class="font-semibold text-tinta">murni (versi 1)</b></span>
        </div>

        <details class="relative ml-auto">
            <summary class="flex cursor-pointer list-none items-center gap-2 rounded-lg p-1 hover:bg-kertas">
                <span class="grid h-[34px] w-[34px] place-items-center rounded-full bg-daun-muda text-sm font-bold text-daun-tua">SA</span>
                <span class="hidden text-left leading-tight md:block">
                    <span class="block font-semibold">Sisfani, A.Md</span>
                    <small class="text-redam">Bendahara pengeluaran</small>
                </span>
                <i class="bi bi-chevron-down text-xs text-redam"></i>
            </summary>
            <div class="absolute right-0 mt-2 w-48 rounded-lg border border-garis bg-white py-1 shadow-lg">
                <a href="#" class="block px-4 py-2 hover:bg-kertas">Profil saya</a>
                <a href="#" class="block px-4 py-2 hover:bg-kertas">Ganti kata sandi</a>
                <form method="POST" action="#" class="border-t border-garis">@csrf<button class="block w-full px-4 py-2 text-left hover:bg-kertas" type="submit">Keluar</button></form>
            </div>
        </details>
    </header>

    <main class="flex-1 px-5 pb-12 pt-6 lg:px-8 lg:pt-7">
        @yield('content')
    </main>
</div>

<script>
    (function () {
        var side = document.getElementById('sidebar'), over = document.getElementById('overlay'), btn = document.getElementById('buka-menu');
        function atur(buka) {
            side.classList.toggle('-translate-x-full', !buka);
            over.classList.toggle('hidden', !buka);
            btn.setAttribute('aria-expanded', buka ? 'true' : 'false');
        }
        btn.addEventListener('click', function () { atur(side.classList.contains('-translate-x-full')); });
        over.addEventListener('click', function () { atur(false); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') atur(false); });
    })();
</script>
@stack('scripts')
</body>
</html>