<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - {{ config('app.name', 'e-SPJ') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/tema-spj.css') }}" rel="stylesheet">
    
    <style>
        body { 
            margin: 0; padding: 0; height: 100vh; overflow: hidden; 
            background: #022c22; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
        }
        
        .login-wrapper { display: flex; height: 100vh; width: 100vw; }

        /* --- SISI KIRI: MEREK & AURORA --- */
        .merek { 
            flex: 1.2;
            display: flex; flex-direction: column; justify-content: space-between; 
            padding: 4rem; position: relative; overflow: hidden;
            color: #d1fae5;
            border-right: 1px solid rgba(255,255,255,0.05); 
        }

        .merek::before {
            content: ''; position: absolute; inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px);
            background-size: 24px 24px; z-index: 0;
        }

        .aurora-blob {
            position: absolute; border-radius: 50%; filter: blur(100px); z-index: 0; opacity: 0.5;
            animation: morph 12s ease-in-out infinite alternate, float 8s infinite alternate;
        }
        .blob-1 { width: 500px; height: 500px; background: rgba(16, 185, 129, 0.4); top: -150px; left: -150px; animation-delay: 0s; }
        .blob-2 { width: 400px; height: 400px; background: rgba(250, 204, 21, 0.15); bottom: -100px; right: -100px; animation-delay: -3s; }

        @keyframes morph {
            0% { border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%; }
            100% { border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%; }
        }
        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, 40px) scale(1.1); }
        }

        .merek-content { position: relative; z-index: 1; }
        
        .tanda { 
            width: 68px; height: 68px; border-radius: 20px; 
            background: linear-gradient(135deg, #facc15, #10b981); 
            color: #022c22; display: grid; place-items: center; font-size: 34px;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
            animation: float-logo 4s ease-in-out infinite;
        }
        @keyframes float-logo {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(16, 185, 129, 0.6); }
        }

        /* --- ANIMASI JUDUL --- */
        .judul-container {
            overflow: hidden; 
            margin-bottom: 1.5rem;
        }
        .judul-reveal {
            color: #fff; font-size: 48px; font-weight: 800; line-height: 1.15; max-width: 22ch; letter-spacing: -1.5px;
            margin: 0;
            animation: slideUpReveal 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(100%);
            animation-delay: 0.3s; 
        }
        @keyframes slideUpReveal {
            to { opacity: 1; transform: translateY(0); }
        }

        .text-gradien {
            background: linear-gradient(90deg, #facc15, #34d399, #facc15);
            background-size: 200% auto;
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            animation: shimmer 4s linear infinite;
        }
        @keyframes shimmer { to { background-position: 200% center; } }

        /* --- ANIMASI KETIK LOOPING (PARAGRAF) --- */
        .merek-desc {
            max-width: 45ch; font-size: 16px; line-height: 1.6; color: #a7f3d0;
            min-height: 80px; /* Menahan layout agar tidak goyang */
        }
        .typing-text { display: inline; }
        .typing-cursor {
            display: inline-block; width: 2px; height: 1.1em; background-color: #10b981;
            vertical-align: middle; margin-left: 4px; animation: blink 0.8s step-end infinite;
        }
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }

        .batang { display: flex; height: 8px; border-radius: 4px; overflow: hidden; background: rgba(255,255,255,.1); width: 100%; max-width: 350px; }
        .batang div { height: 100%; border-radius: 4px; }

        /* --- SISI KANAN: FORM (DARK MODE) --- */
        .login-right { 
            flex: 1; 
            background: radial-gradient(circle at 100% 0%, #064e3b 0%, #022c22 60%);
            display: flex; align-items: center; justify-content: center; 
            padding: 40px; position: relative;
        }

        .form-glass {
            width: 100%; max-width: 440px;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 32px;
            padding: 48px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative; z-index: 1;
        }
        
        .form-glass h2 { font-size: 30px; font-weight: 800; color: #ffffff; margin-bottom: 8px; letter-spacing: -1px; }
        .form-glass p { color: #a7f3d0; font-size: 15px; margin-bottom: 36px; }

        .input-float { position: relative; margin-bottom: 24px; }
        .input-float .form-control { 
            padding: 24px 20px 8px 20px; height: 64px; border-radius: 16px; 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            background-color: rgba(0, 0, 0, 0.2); 
            font-size: 15px; font-weight: 600; color: #ffffff;
            transition: all 0.3s ease;
        }
        .input-float label {
            position: absolute; top: 50%; left: 20px; transform: translateY(-50%);
            color: #6ee7b7; font-size: 15px; font-weight: 500; pointer-events: none;
            transition: all 0.2s ease-out;
        }
        .input-float .form-control:focus,
        .input-float .form-control:not(:placeholder-shown) {
            border-color: #10b981; background-color: rgba(0, 0, 0, 0.4); 
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
        }
        .input-float .form-control:focus + label,
        .input-float .form-control:not(:placeholder-shown) + label {
            top: 18px; font-size: 11px; font-weight: 800; color: #10b981; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .input-float .form-control:focus { outline: none; }

        .input-icon { position: absolute; right: 20px; top: 50%; transform: translateY(-50%); color: #6ee7b7; font-size: 20px; transition: all 0.3s ease; pointer-events: none; opacity: 0.5; }
        .input-float:focus-within .input-icon { color: #10b981; opacity: 1; transform: translateY(-50%) scale(1.1); }

        .btn-toggle-pass { position: absolute; right: 50px; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: #6ee7b7; z-index: 5; padding: 8px; transition: all 0.3s ease; opacity: 0.7; }
        .btn-toggle-pass:hover { color: #10b981; opacity: 1; }

        .btn-login {
            width: 100%; padding: 18px; border-radius: 16px;
            background: linear-gradient(135deg, #10b981, #047857);
            color: white; font-weight: 800; font-size: 16px; border: none; letter-spacing: 0.5px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
            margin-top: 10px; position: relative; overflow: hidden;
        }
        .btn-login:hover { transform: translateY(-4px) scale(1.02); box-shadow: 0 15px 35px rgba(16, 185, 129, 0.5); }
        .btn-login:active { transform: translateY(0) scale(0.98); }
        
        .btn-login::before {
            content: ''; position: absolute; top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transform: skewX(-25deg); transition: all 0.6s ease;
        }
        .btn-login:hover::before { left: 150%; }

        .form-check-input { background-color: rgba(0,0,0,0.3); border-color: rgba(255,255,255,0.2); }
        .form-check-input:checked { background-color: #10b981; border-color: #10b981; }
        .form-check-label { color: #a7f3d0; }

        .animate-up { animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; transform: translateY(30px); }
        @keyframes slideUp { to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 991px) {
            .login-wrapper { flex-direction: column; overflow-y: auto; height: auto; min-height: 100vh; }
            .merek { display: none; }
            .login-right { padding: 24px; }
            .form-glass { padding: 32px 24px; }
        }
    </style>
</head>
<body>

<div class="login-wrapper" id="loginWrapper">
    {{-- KIRI: MEREK --}}
    <div class="merek d-none d-lg-flex">
        <div class="aurora-blob blob-1"></div>
        <div class="aurora-blob blob-2"></div>

        <div class="merek-content animate-up" style="animation-delay: 0.1s;">
            <div class="d-flex align-items-center gap-3">
                <span class="tanda"><i class="bi bi-receipt-cutoff"></i></span>
                <div>
                    <strong class="text-white fs-4 d-block lh-1" style="letter-spacing: -0.5px;">e-SPJ</strong>
                    <span class="text-emerald-200 small fw-semibold" style="color: #6ee7b7;">Sistem Digital</span>
                </div>
            </div>
        </div>

        <div class="merek-content animate-up" style="animation-delay: 0.2s;">
            <div class="judul-container">
                <h1 class="judul-reveal">
                    Input SPJ sekali,<br>
                    <span class="text-gradien">dokumen siap cetak.</span>
                </h1>
            </div>
            
            <div class="merek-desc mt-3 mb-0">
                <span id="typing-text"></span><span class="typing-cursor"></span>
            </div>
        </div>

        <div class="merek-content animate-up" style="animation-delay: 0.5s;">
            <div class="batang mb-3" aria-hidden="true">
                <div style="width:56%; background: #10b981;"></div>
                <div style="width:8%; background: #facc15;"></div>
            </div>
            <small class="opacity-75 fw-medium"><i class="bi bi-shield-check me-1"></i> Sisa pagu dicek otomatis di setiap SPJ.</small>
        </div>
    </div>

    {{-- KANAN: FORM KACA GELAP --}}
    <div class="login-right">
        <div class="form-glass animate-up" style="animation-delay: 0.2s;">
            
            <div class="d-flex align-items-center gap-2 mb-4 d-lg-none">
                <span style="width:44px;height:44px;border-radius:12px;background:#10b981;color:#022c22;display:grid;place-items:center;font-size:1.4rem"><i class="bi bi-receipt-cutoff"></i></span>
                <strong class="fs-4 fw-bold text-white">e-SPJ</strong>
            </div>

            <h2>Masuk ke akun</h2>
            <p>Gunakan email atau NIP yang didaftarkan admin OPD.</p>

            @error('login')
                <div class="alert alert-danger border-0 shadow-sm rounded-4 py-3 animate-up" style="background: rgba(239, 68, 68, 0.1); color: #fca5a5;" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>{{ $message }}
                </div>
            @enderror

            <form method="POST" action="{{ route('login.proses') }}" id="formLogin" novalidate>
                @csrf
                
                <div class="animate-up" style="animation-delay: 0.4s;">
                    <div class="input-float">
                        <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus autocomplete="username" class="form-control @error('login') is-invalid @enderror" placeholder=" ">
                        <label for="login">Email atau NIP</label>
                        <i class="bi bi-person input-icon"></i>
                    </div>
                </div>

                <div class="animate-up" style="animation-delay: 0.5s;">
                    <div class="input-float">
                        <input id="password" name="password" type="password" required autocomplete="current-password" class="form-control @error('password') is-invalid @enderror" placeholder=" ">
                        <label for="password">Kata Sandi</label>
                        <i class="bi bi-lock input-icon"></i>
                        <button class="btn-toggle-pass" type="button" id="tampil" aria-label="Tampilkan kata sandi">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    @error('password')<div class="text-danger small mt-1 px-2" style="color: #fca5a5;">{{ $message }}</div>@enderror
                </div>

                <div class="form-check mb-4 animate-up px-2" style="animation-delay: 0.6s;">
                    <input class="form-check-input shadow-none" type="checkbox" name="ingat" id="ingat" value="1">
                    <label class="form-check-label small fw-medium mt-1" for="ingat">Ingat saya di perangkat ini</label>
                </div>

                <button type="submit" class="btn-login animate-up" style="animation-delay: 0.7s;" id="btnSubmit">
                    Masuk ke Sistem <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </form>

            <p class="small mt-5 mb-0 animate-up text-center" style="color: #6ee7b7; animation-delay: 0.8s;">
                Lupa kata sandi? Hubungi admin OPD Anda untuk mengatur ulang.
            </p>
        </div>
    </div>
</div>

<script>
    // --- 1. Toggle Password ---
    (function () {
        var tombol = document.getElementById('tampil'), isi = document.getElementById('password');
        tombol.addEventListener('click', function () {
            var tampak = isi.type === 'text';
            isi.type = tampak ? 'password' : 'text';
            tombol.setAttribute('aria-pressed', tampak ? 'false' : 'true');
            tombol.setAttribute('aria-label', tampak ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
            tombol.firstElementChild.className = tampak ? 'bi bi-eye' : 'bi bi-eye-slash';
        });
    })();

    // --- 2. ANIMASI KETIK LOOPING (TERUS MENERUS) ---
    document.addEventListener('DOMContentLoaded', function() {
        const textToType = "Nota pesanan, berita acara, dan kwitansi dibuat otomatis dari satu data, lengkap dengan verifikasi berjenjang.";
        const typingElement = document.getElementById('typing-text');
        
        let index = 0;
        let isDeleting = false;

        function typeLoop() {
            const currentText = textToType.substring(0, index);
            typingElement.textContent = currentText;

            let typeSpeed = 40; // Kecepatan ketik

            if (isDeleting) {
                typeSpeed /= 2; // Hapus lebih cepat
                index--;
            } else {
                index++;
            }

            // Jika teks sudah selesai diketik semua
            if (!isDeleting && index > textToType.length) {
                isDeleting = true;
                typeSpeed = 2000; // Jeda 2 detik sebelum mulai menghapus
            } 
            // Jika teks sudah terhapus semua
            else if (isDeleting && index === 0) {
                isDeleting = false;
                typeSpeed = 500; // Jeda 0.5 detik sebelum mulai mengetik lagi
            }

            setTimeout(typeLoop, typeSpeed);
        }

        // Mulai animasi setelah 2 detik (agar animasi judul selesai dulu)
        setTimeout(typeLoop, 2000); 
    });

    // --- 3. Animasi saat submit ---
    document.getElementById('formLogin').addEventListener('submit', function(e) {
        document.getElementById('btnSubmit').innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Memproses...';
        document.getElementById('loginWrapper').style.opacity = '0';
    });
</script>
</body>
</html>