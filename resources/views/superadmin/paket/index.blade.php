@extends('layouts.app')
@section('title', 'Manajemen Paket')
@section('nav', 'superadmin-paket')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title mb-0">Manajemen Paket Langganan</h1>
    </div>

    @if(session('sukses'))
        <div class="alert alert-success">{{ session('sukses') }}</div>
    @endif

    <div class="row">
        {{-- Form Tambah Paket --}}
        <div class="col-md-5">
            <div class="card-tabel p-4 mb-4">
                <h5 class="fw-bold mb-3">Tambah Paket Baru</h5>
                <form action="{{ route('superadmin.paket.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama Paket</label>
                        <input type="text" name="nama" class="form-control" placeholder="Cth: Paket Pro" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga Bulanan (Rp)</label>
                        <input type="number" name="harga_bulanan" class="form-control" placeholder="Cth: 500000" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga Tahunan (Rp) <span class="text-muted fw-normal">(Opsional)</span></label>
                        <input type="number" name="harga_tahunan" class="form-control" placeholder="Cth: 5000000">
                        <div class="form-text small">Kosongkan jika paket ini tidak memiliki opsi tahunan.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fitur (Pisahkan dengan koma)</label>
                        <textarea name="fitur" class="form-control" rows="3" placeholder="Cth: Cetak PDF, Import DPA, Prioritas Support" required></textarea>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_populer" id="is_populer" value="1">
                        <label class="form-check-label" for="is_populer">Tandai sebagai "Paling Populer"</label>
                    </div>
                    <button type="submit" class="btn btn-premium w-100">Simpan Paket</button>
                </form>
            </div>

            {{-- Form Buat Tagihan Baru --}}
            @if($pakets->count() > 0)
            <div class="card-tabel p-4" style="border-left: 4px solid #f59e0b;">
                <h5 class="fw-bold mb-3 text-warning">Buat Tagihan Baru</h5>
                <p class="small text-secondary mb-3">Pilih OPD dan Paket untuk menerbitkan tagihan (Invoice).</p>
                <form action="{{ route('superadmin.paket.buat-tagihan') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Pilih OPD</label>
                        <select name="opd_id" class="form-select" required>
                            <option value="">-- Pilih OPD --</option>
                            @foreach(\App\Models\Opd::orderBy('nama')->get() as $opd)
                                <option value="{{ $opd->id }}">{{ $opd->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Pilih Paket --}}
                    <div class="mb-3">
                        <label class="form-label">Pilih Paket</label>
                        <select name="paket_id" id="pilih_paket_tagihan" class="form-select" required>
                            <option value="" data-harga-bulanan="0" data-harga-tahunan="0">-- Pilih Paket --</option>
                            @foreach($pakets as $p)
                                <option value="{{ $p->id }}" 
                                        data-harga-bulanan="{{ $p->harga_bulanan }}" 
                                        data-harga-tahunan="{{ $p->harga_tahunan }}">
                                    {{ $p->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Pilih Durasi --}}
                    <div class="mb-3">
                        <label class="form-label">Durasi Tagihan</label>
                        <select name="durasi" id="pilih_durasi_tagihan" class="form-select" required>
                            <option value="bulanan">Bulanan (1 Bulan)</option>
                            <option value="tahunan">Tahunan (12 Bulan)</option>
                        </select>
                    </div>

                    {{-- Info Harga Dinamis --}}
                    <div class="alert alert-info py-2 small" id="info_harga_tagihan">
                        Total Tagihan: <strong id="text_total_tagihan">Rp0</strong>
                    </div>

                    <button type="submit" class="btn btn-warning w-100 fw-bold">Terbitkan Tagihan</button>
                </form>
            </div>
            @endif
        </div>

        {{-- Tabel Daftar Paket --}}
        <div class="col-md-7">
            <div class="card-tabel p-4">
                <h5 class="fw-bold mb-3">Daftar Paket</h5>
                                <table class="table tabel-modern w-100">
                    <thead>
                        <tr>
                            <th>Nama Paket</th>
                            <th>Harga Bulanan</th>
                            <th>Harga Tahunan</th>
                            <th>Fitur</th>
                            <th>Populer?</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pakets as $p)
                        <tr>
                            <td class="fw-bold">{{ $p->nama }}</td>
                            <td>Rp{{ number_format($p->harga_bulanan, 0, ',', '.') }}</td>
                            <td>
                                @if($p->harga_tahunan)
                                    Rp{{ number_format($p->harga_tahunan, 0, ',', '.') }}
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <ul class="mb-0 ps-3 small">
                                    @if(is_array($p->fitur))
                                        @foreach($p->fitur as $f)
                                            <li>{{ $f }}</li>
                                        @endforeach
                                    @endif
                                </ul>
                            </td>
                            <td>
                                @if($p->is_populer)
                                    <span class="badge bg-success">Ya</span>
                                @else
                                    <span class="badge bg-secondary">Tidak</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada paket yang ditambahkan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectPaket = document.getElementById('pilih_paket_tagihan');
        const selectDurasi = document.getElementById('pilih_durasi_tagihan');
        const textTotal = document.getElementById('text_total_tagihan');

        function updateHarga() {
            if (!selectPaket || !selectDurasi || !textTotal) return;

            const opt = selectPaket.options[selectPaket.selectedIndex];
            const hargaBulanan = parseFloat(opt.getAttribute('data-harga-bulanan')) || 0;
            const hargaTahunan = parseFloat(opt.getAttribute('data-harga-tahunan')) || 0;
            const durasi = selectDurasi.value;

            let total = 0;
            if (durasi === 'tahunan') {
                total = hargaTahunan;
            } else {
                total = hargaBulanan;
            }

            textTotal.innerText = 'Rp' + new Intl.NumberFormat('id-ID').format(total);
        }

        if (selectPaket) selectPaket.addEventListener('change', updateHarga);
        if (selectDurasi) selectDurasi.addEventListener('change', updateHarga);
        
        // Panggil saat pertama kali load
        updateHarga();
    });
</script>
@endpush
@endsection