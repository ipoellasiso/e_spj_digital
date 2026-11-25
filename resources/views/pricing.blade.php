<!doctype html>
<html lang="id">
<head>
    <!-- ... (Sertakan Bootstrap, Google Fonts, dll) ... -->
    <style>
        body { background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; }
        .pricing-card { transition: all 0.4s ease; border-radius: 24px; border: 1px solid #e2e8f0; background: #fff; }
        .pricing-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px -10px rgba(16, 185, 129, 0.15); border-color: #10b981; }
        .card-populer { border: 2px solid #10b981; position: relative; }
        .badge-populer { position: absolute; top: -15px; left: 50%; transform: translateX(-50%); background: #10b981; color: white; padding: 6px 20px; border-radius: 99px; font-weight: 700; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container py-5 mt-5">
        <div class="text-center mb-5">
            <h1 class="fw-bold">Pilih Paket Langganan Anda</h1>
            <p class="text-secondary">Tingkatkan efisiensi pengelolaan keuangan OPD Anda dengan e-SPJ.</p>
        </div>

        <div class="row justify-content-center g-4">
            @foreach($pakets as $paket)
            <div class="col-md-4">
                <div class="pricing-card p-4 h-100 {{ $paket->is_populer ? 'card-populer shadow-lg' : 'shadow-sm' }}">
                    @if($paket->is_populer)
                        <div class="badge-populer">PALING BANYAK DIPILIH</div>
                    @endif
                    
                    <h4 class="fw-bold mt-2">{{ $paket->nama }}</h4>
                    <div class="my-4">
                        <span class="fs-2 fw-bold text-success">Rp{{ number_format($paket->harga_bulanan, 0, ',', '.') }}</span>
                        <span class="text-secondary">/ bulan</span>
                    </div>
                    
                    <ul class="list-unstyled mb-4">
                        @foreach($paket->fitur as $fitur)
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> {{ $fitur }}</li>
                        @endforeach
                    </ul>

                    <a href="{{ route('register', ['paket_id' => $paket->id]) }}" class="btn btn-success w-100 py-3 fw-bold" style="border-radius: 12px; background: #064e3b;">
                        Pilih Paket & Daftar
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</body>
</html>