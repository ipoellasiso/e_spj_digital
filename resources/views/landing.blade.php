<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Review Realisasi Anggaran - e-SPJ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* =========================================
           CORE & VARIABLES
           ========================================= */
        :root {
            --hijau-900: #022c22;
            --hijau-800: #064e3b;
            --hijau-600: #059669;
            --hijau-500: #10b981;
            --kuning-400: #facc15;
            --bg-light: #f8fafc;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg-light); 
            color: #1e293b; 
            overflow-x: hidden;
        }

        /* Efek Mesh Gradient di Background */
        .bg-mesh {
            position: fixed; inset: 0; z-index: -1; pointer-events: none;
            background-image: 
                radial-gradient(at 0% 0%, rgba(16, 185, 129, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(250, 204, 21, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.1) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(59, 130, 246, 0.05) 0px, transparent 50%);
        }

        /* =========================================
           ANIMASI KEYFRAMES
           ========================================= */
        @keyframes slideUpFade {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 15px rgba(16, 185, 129, 0.2); }
            50% { box-shadow: 0 0 30px rgba(16, 185, 129, 0.5); }
        }
        @keyframes shimmerText {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }
        @keyframes spinSlow {
            100% { transform: rotate(360deg); }
        }

        /* Utility Classes untuk Animasi */
        .anim-up { animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) both; }
        .d-1 { animation-delay: 0.1s; }
        .d-2 { animation-delay: 0.2s; }
        .d-3 { animation-delay: 0.3s; }
        .d-4 { animation-delay: 0.4s; }

        /* =========================================
           NAVBAR
           ========================================= */
        .navbar-custom { 
            background: rgba(255, 255, 255, 0.7); 
            backdrop-filter: blur(16px); 
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255,255,255,0.5); 
            padding: 16px 32px; 
            position: sticky; top: 0; z-index: 1000;
            transition: all 0.3s ease;
        }
        .brand-logo { 
            width: 44px; height: 44px; background: linear-gradient(135deg, var(--hijau-500), var(--hijau-800)); 
            color: white; border-radius: 14px; display: grid; place-items: center; font-size: 22px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            animation: float 4s ease-in-out infinite;
        }
        
        /* =========================================
           HERO SECTION (Judul & Teks Berkilau)
           ========================================= */
        .hero-section { padding: 80px 24px 50px; text-align: center; }
        .hero-badge {
            display: inline-block; padding: 6px 16px; border-radius: 99px;
            background: rgba(16, 185, 129, 0.1); color: var(--hijau-800);
            font-size: 13px; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 20px;
            border: 1px solid rgba(16, 185, 129, 0.2);
            animation: slideUpFade 0.6s ease both;
        }
        .hero-title { 
            font-weight: 800; font-size: 3.2rem; letter-spacing: -1.5px; line-height: 1.1; margin-bottom: 16px;
        }
        .text-gradient-animated {
            background: linear-gradient(90deg, var(--hijau-600), var(--hijau-500), var(--kuning-400), var(--hijau-500), var(--hijau-600));
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shimmerText 4s linear infinite;
        }
        .hero-desc { color: #64748b; max-width: 700px; margin: 0 auto; font-size: 16px; line-height: 1.6; font-weight: 500; }

        /* =========================================
           CARD OPD (Glassmorphism & Hover 3D)
           ========================================= */
        .card-opd { 
            background: rgba(255, 255, 255, 0.85); 
            backdrop-filter: blur(20px);
            border-radius: 24px; border: 1px solid rgba(255, 255, 255, 0.8); 
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0,0,0,0.02); 
            margin-bottom: 32px; overflow: hidden; 
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }
        .card-opd:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px -15px rgba(16, 185, 129, 0.15);
        }
        
        .card-header-opd { 
            padding: 28px 36px; 
            background: linear-gradient(135deg, var(--hijau-900), var(--hijau-800)); 
            color: white; display: flex; justify-content: space-between; align-items: center; 
            position: relative; overflow: hidden;
        }
        /* Efek kilau melintas di header */
        .card-header-opd::before {
            content: ""; position: absolute; top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transform: skewX(-25deg); animation: shine 6s infinite;
        }
        @keyframes shine { 100% { left: 200%; } }
        
        .opd-name { font-weight: 800; font-size: 1.5rem; margin: 0; letter-spacing: -0.5px; position: relative; z-index: 1; }
        .badge-aktif {
            background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3); color: #fff;
            padding: 8px 16px; border-radius: 99px; font-weight: 600; font-size: 13px;
            backdrop-filter: blur(4px); position: relative; z-index: 1;
            animation: pulseGlow 3s infinite;
        }

        /* =========================================
           SUMMARY GRID (Pagu, Realisasi, Sisa)
           ========================================= */
        .summary-grid { 
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; 
            padding: 32px 36px; border-bottom: 1px solid rgba(0,0,0,0.04); background: rgba(248, 250, 252, 0.5); 
        }
        .summary-item { 
            padding: 24px; background: #fff; border-radius: 20px; 
            border: 1px solid rgba(0,0,0,0.04); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); 
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative; overflow: hidden;
        }
        .summary-item:hover {
            transform: translateY(-5px) scale(1.02);
            border-color: rgba(16, 185, 129, 0.3);
            box-shadow: 0 15px 30px -10px rgba(16, 185, 129, 0.1);
        }
        .summary-label { font-size: 12px; font-weight: 800; text-transform: uppercase; color: #64748b; letter-spacing: 1px; margin-bottom: 8px; }
        .summary-value { font-size: 1.6rem; font-weight: 800; color: #0f172a; }
        
        /* =========================================
           HIERARKI (TREE) & LEAF
           ========================================= */
        .tree-container { padding: 36px; }
        .tree-item { margin-bottom: 12px; }
        
        /* Header Tree (Klik) */
        .tree-header { 
            display: flex; align-items: center; justify-content: space-between; 
            padding: 18px 24px; background: #fff; border: 1px solid #e2e8f0; 
            border-radius: 16px; cursor: pointer; transition: all 0.3s ease; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .tree-header:hover { 
            background: #f0fdf4; border-color: var(--hijau-500); 
            transform: translateX(5px); box-shadow: 0 8px 15px -5px rgba(16, 185, 129, 0.1);
        }
        .tree-header-left { display: flex; align-items: center; gap: 16px; font-size: 15px; font-weight: 700; color: #1e293b; }
        .tree-icon { color: var(--hijau-500); transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); font-size: 16px; }
        
        .tree-item.open > .tree-header .tree-icon { transform: rotate(90deg); }
        .tree-item.open > .tree-header { border-color: var(--hijau-500); background: #f0fdf4; }
        
        /* Konten Tree */
        .tree-content { 
            display: none; padding-left: 36px; margin-top: 10px; 
            border-left: 2px dashed #cbd5e1; margin-left: 32px; 
        }
        .tree-item.open > .tree-content { display: block; animation: fadeIn 0.4s ease; }
        
        /* Leaf (Rekening Belanja) */
        .tree-leaf { 
            display: flex; justify-content: space-between; align-items: center; 
            padding: 20px 24px; font-size: 14px; background: #fff; 
            border-radius: 16px; margin-bottom: 12px; border: 1px solid #e2e8f0; 
            transition: all 0.3s ease; position: relative; overflow: hidden;
        }
        /* Garis aksen kiri pada leaf */
        .tree-leaf::before {
            content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 4px;
            background: var(--hijau-500); opacity: 0; transition: opacity 0.3s;
        }
        .tree-leaf:hover {
            border-color: var(--hijau-500);
            box-shadow: 0 10px 20px -10px rgba(16, 185, 129, 0.15);
            transform: translateX(5px);
        }
        .tree-leaf:hover::before { opacity: 1; }

        .tree-leaf-left { flex: 1; display: flex; align-items: center; gap: 16px; }
        .tree-leaf-right { display: flex; gap: 32px; text-align: right; }
        
        .leaf-stat { display: flex; flex-direction: column; min-width: 120px; }
        .leaf-stat-label { font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px; margin-bottom: 4px; }
        .leaf-stat-value { font-size: 15px; font-weight: 800; color: #1e293b; }
        .val-realisasi { color: #d97706; }
        .val-sisa { color: var(--hijau-600); }

        /* Badge Kode */
        .kode-badge { 
            font-size: 12px; font-weight: 800; color: var(--hijau-800); 
            background: rgba(16, 185, 129, 0.1); padding: 6px 12px; border-radius: 8px; 
            white-space: nowrap; border: 1px solid rgba(16, 185, 129, 0.2);
            transition: all 0.3s ease;
        }
        .tree-header:hover .kode-badge, .tree-leaf:hover .kode-badge {
            background: var(--hijau-600); color: #fff; border-color: var(--hijau-600);
        }
        
        /* =========================================
           TOMBOL LOGIN
           ========================================= */
        .btn-login-top { 
            background: linear-gradient(135deg, var(--hijau-800), var(--hijau-900)); 
            color: white; font-weight: 700; border-radius: 12px; padding: 12px 28px; 
            text-decoration: none; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
            box-shadow: 0 4px 15px rgba(6, 78, 59, 0.3);
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-login-top:hover { 
            background: linear-gradient(135deg, var(--hijau-600), var(--hijau-800)); 
            color: white; transform: translateY(-3px) scale(1.02); 
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4); 
        }
    </style>
</head>
<body>

    <div class="bg-mesh"></div>

    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center gap-3" href="{{ route('landing') }}">
                <div class="brand-logo"><i class="bi bi-receipt-cutoff"></i></div>
                <span class="fw-bolder fs-3 text-dark" style="letter-spacing: -1px;">e-SPJ <span class="text-gradient-animated">Publik</span></span>
            </a>
            
            {{-- Bungkus tombol dengan d-flex agar rapi --}}
            <div class="ms-auto d-flex align-items-center gap-2 gap-md-3">
                <a href="{{ route('register') }}" class="btn btn-outline-success fw-bold d-none d-md-inline-flex" style="border-radius: 10px; padding: 10px 20px; border-width: 2px;">
                    Daftar OPD Baru
                </a>
                <a href="{{ route('login') }}" class="btn-login-top">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk ke Sistem
                </a>
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section class="hero-section">
        <div class="container-fluid px-4">
            <div class="hero-badge anim-up">✨ Transparansi Anggaran Digital</div>
            <h1 class="hero-title anim-up d-1">
                Pantau Realisasi <br>
                <span class="text-gradient-animated">Secara Transparan.</span>
            </h1>
            <p class="hero-desc anim-up d-2">
                Rincian penggunaan anggaran dari tingkat Kegiatan, Sub Kegiatan, hingga Rekening Belanja. Data disajikan secara *real-time* dan akurat.
            </p>
        </div>
    </section>

    {{-- Konten Utama --}}
    <div class="container-fluid px-4 pb-5">
        @if($opdTerpilih && $dataDpa)
            <div class="card-opd anim-up d-3">
                
                {{-- Header OPD --}}
                <div class="card-header-opd">
                    <div>
                        <h3 class="opd-name">{{ $opdTerpilih->nama }}</h3>
                        <div class="mt-1 fw-medium" style="color: #a7f3d0; font-size: 14px;">
                            <i class="bi bi-calendar3 me-1"></i> DPA {{ $dataDpa['versi']->jenis }} Versi {{ $dataDpa['versi']->nomor_versi }} - Tahun {{ $tahun }}
                        </div>
                    </div>
                    <span class="badge-aktif"><i class="bi bi-shield-check me-1"></i> Data Aktif</span>
                </div>

                {{-- Ringkasan Pagu --}}
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-label">Total Pagu</div>
                        <div class="summary-value">Rp{{ number_format($dataDpa['total_pagu'], 0, ',', '.') }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Realisasi Final</div>
                        <div class="summary-value text-success">Rp{{ number_format($dataDpa['total_final'], 0, ',', '.') }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Sisa Pagu</div>
                        <div class="summary-value text-primary">Rp{{ number_format($dataDpa['total_sisa'], 0, ',', '.') }}</div>
                    </div>
                </div>

                {{-- Hierarki (Tree) --}}
                <div class="tree-container">
                    @foreach($dataDpa['kegiatan'] as $keg)
                        <div class="tree-item">
                            {{-- LEVEL 1: KEGIATAN --}}
                            <div class="tree-header" onclick="toggleTree(this)">
                                <div class="tree-header-left">
                                    <i class="bi bi-chevron-right tree-icon"></i>
                                    <span class="kode-badge">{{ $keg->kode }}</span>
                                    <span>{{ $keg->uraian }}</span>
                                </div>
                            </div>
                            
                            <div class="tree-content">
                                @foreach($keg->subKegiatan as $sub)
                                    {{-- LEVEL 2: SUB KEGIATAN --}}
                                    <div class="tree-item">
                                        <div class="tree-header" onclick="toggleTree(this)">
                                            <div class="tree-header-left">
                                                <i class="bi bi-chevron-right tree-icon"></i>
                                                <span class="kode-badge">{{ $sub->kode }}</span>
                                                <span>{{ $sub->uraian }}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="tree-content">
                                            {{-- LEVEL 3: REKENING BELANJA --}}
                                            @foreach($sub->anggaranRekening as $rek)
                                                <div class="tree-leaf">
                                                    <div class="tree-leaf-left">
                                                        <span class="kode-badge">{{ $rek->rekening->kode }}</span>
                                                        <span class="fw-bold text-dark">{{ $rek->rekening->nama }}</span>
                                                    </div>

                                                    <div class="tree-leaf-right">
                                                        <div class="leaf-stat">
                                                            <span class="leaf-stat-label">Pagu</span>
                                                            <span class="leaf-stat-value">Rp{{ number_format($rek->pagu, 0, ',', '.') }}</span>
                                                        </div>
                                                        <div class="leaf-stat">
                                                            <span class="leaf-stat-label">Realisasi</span>
                                                            <span class="leaf-stat-value val-realisasi">Rp{{ number_format($rek->realisasi_final + $rek->reserved, 0, ',', '.') }}</span>
                                                        </div>
                                                        <div class="leaf-stat">
                                                            <span class="leaf-stat-label">Sisa</span>
                                                            <span class="leaf-stat-value val-sisa">Rp{{ number_format($rek->sisa, 0, ',', '.') }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif($opdTerpilih && !$dataDpa)
            <div class="alert alert-warning text-center shadow-sm border-0 rounded-4 p-5 mx-4 anim-up d-3">
                <i class="bi bi-exclamation-triangle-fill fs-1 text-warning mb-3 d-block"></i>
                <h4 class="fw-bold">Data DPA Tidak Ditemukan</h4>
                <p class="mb-0 fs-5">OPD <strong>{{ $opdTerpilih->nama }}</strong> belum memiliki DPA aktif untuk tahun {{ $tahun }}.</p>
            </div>
        @else
            <div class="text-center text-muted py-5 anim-up d-3">
                <i class="bi bi-building fs-1 opacity-25 mb-3 d-block"></i>
                <h5 class="fw-semibold">Data Belum Tersedia.</h5>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fungsi Buka Tutup Tree
        function toggleTree(element) {
            const parentItem = element.closest('.tree-item');
            parentItem.classList.toggle('open');
        }

        // Efek Scroll (Navbar mengecil / berubah warna)
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-custom');
            if (window.scrollY > 20) {
                navbar.style.padding = '8px 32px';
                navbar.style.boxShadow = '0 10px 30px -10px rgba(0,0,0,0.05)';
            } else {
                navbar.style.padding = '16px 32px';
                navbar.style.boxShadow = 'none';
            }
        });
    </script>
</body>
</html>