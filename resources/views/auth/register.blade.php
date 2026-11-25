<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Langganan - e-SPJ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { background: #022c22; font-family: 'Plus Jakarta Sans', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .register-card {
            background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 24px;
            padding: 40px; width: 100%; max-width: 600px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
        }
        .form-control, .form-select {
            background-color: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 12px 16px; border-radius: 10px;
        }
        .form-control:focus, .form-select:focus {
            background-color: rgba(0,0,0,0.4); border-color: #10b981; color: #fff; box-shadow: 0 0 0 4px rgba(16,185,129,0.15);
        }
        .form-label { color: #a7f3d0; font-weight: 600; font-size: 13px; }
        .form-select option { background: #022c22; color: #fff; }
        .btn-daftar { background: linear-gradient(135deg, #10b981, #047857); color: white; font-weight: 700; border-radius: 10px; padding: 14px; width: 100%; border: none; transition: all 0.3s; }
        .btn-daftar:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(16,185,129,0.3); color: white; }
    </style>
</head>
<body>

<div class="register-card">
    <div class="text-center mb-4">
        <div class="d-inline-grid place-items-center rounded-3 mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #10b981, #047857); color: white; font-size: 28px;">
            <i class="bi bi-person-plus"></i>
        </div>
        <h3 class="fw-bold text-white mb-1">Daftar Langganan OPD</h3>
        <p class="text-white-50 small">Isi data instansi Anda untuk mulai menggunakan e-SPJ.</p>
    </div>

    @if(session('error'))
        <div class="alert alert-danger py-2 small">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger py-2 small">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('register.proses') }}" method="POST">
        @csrf
        
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">Kode OPD</label>
                <input type="text" name="kode" class="form-control" placeholder="Cth: 7.01.00..." required value="{{ old('kode') }}">
            </div>
            <div class="col-md-8">
                <label class="form-label">Nama Instansi / OPD</label>
                <input type="text" name="nama_opd" class="form-control" placeholder="Cth: Dinas Pendidikan" required value="{{ old('nama_opd') }}">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Singkatan OPD</label>
            <input type="text" name="singkatan" class="form-control" placeholder="Cth: DISDIK (Maks. 30 Karakter)" required value="{{ old('singkatan') }}" maxlength="30">
            <div class="form-text text-white-50" style="font-size: 11px;">Singkatan akan tampil di menu dan dokumen cetak.</div>
        </div>

        <div class="mb-3">
            <label class="form-label">Nama Admin (Penanggung Jawab)</label>
            <input type="text" name="nama_admin" class="form-control" placeholder="Nama Lengkap Anda" required value="{{ old('nama_admin') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Email (Gunakan Email Aktif)</label>
            <input type="email" name="email" class="form-control" placeholder="email@instansi.go.id" required value="{{ old('email') }}">
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">Kata Sandi</label>
                <input type="password" name="password" class="form-control" required minlength="8">
            </div>
            <div class="col-md-6">
                <label class="form-label">Konfirmasi Sandi</label>
                <input type="password" name="password_confirmation" class="form-control" required minlength="8">
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label">Pilih Paket Langganan</label>
            <select name="paket_id" class="form-select" required>
                <option value="">-- Pilih Paket --</option>
                @foreach($daftarPaket as $p)
                    <option value="{{ $p->id }}" {{ old('paket_id', $paket_id) == $p->id ? 'selected' : '' }}>
                        {{ $p->nama }} - Rp{{ number_format($p->harga_bulanan, 0, ',', '.') }}/bulan
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn-daftar">Daftar & Buat Tagihan</button>
    </form>

    <div class="text-center mt-4">
        <span class="text-white-50 small">Sudah punya akun?</span>
        <a href="{{ route('login') }}" class="text-decoration-none fw-bold small ms-1" style="color: #34d399;">Masuk di sini</a>
    </div>
</div>

</body>
</html>