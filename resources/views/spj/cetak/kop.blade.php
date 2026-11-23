<div class="kop-surat d-flex align-items-center justify-content-center gap-3">
    {{-- Ganti src dengan logo OPD jika ada --}}
    <img src="{{ asset('images/logo-pemda.png') }}" alt="Logo" style="height: 70px; width: auto;">
    <div class="text-center">
        <h4 style="margin: 0; font-size: 18px; font-weight: bold;">PEMERINTAH KOTA PALU</h4>
        <h5 style="margin: 0; font-size: 20px; font-weight: bold;">{{ strtoupper($opd->nama) }}</h5>
        <p style="margin: 0; font-size: 12px;">{{ $opd->alamat ?? 'Jl. Alamat Kantor No. 123, Kota Palu, Sulawesi Tengah' }}</p>
        <p style="margin: 0; font-size: 12px;">Telp: (0451) 123456 | Email: info@example.com</p>
    </div>
</div>