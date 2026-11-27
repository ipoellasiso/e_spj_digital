@extends('layouts.app')

@section('title', $spj->exists ? 'Ubah SPJ' : 'Buat SPJ')
@section('nav', $spj->exists ? 'spj' : 'spj-baru')

@push('styles')
<style>
    /* =========================================
       CSS OPTIMASI FULL SCREEN & COMPACT
       ========================================= */
    .form-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    }
    .form-card h2 {
        font-size: 1rem;
        font-weight: 700;
        color: #166534;
        margin-bottom: 1rem;
    }
    .form-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 0.3rem;
    }
    .form-select, .form-control {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.85rem;
        color: #1e293b !important; /* Paksa warna teks gelap */
        padding: 0.5rem 0.75rem;
    }
    .form-select:focus, .form-control:focus {
        background-color: #ffffff;
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
    }
    .btn-premium {
        background-color: #10b981;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s;
    }
    .btn-premium:hover {
        background-color: #059669;
        color: white;
        transform: translateY(-1px);
    }
    .btn-batal {
        background-color: #ffffff;
        color: #64748b;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s;
    }
    .btn-batal:hover {
        background-color: #f1f5f9;
        color: #334155;
    }
    .back-link {
        font-size: 0.85rem;
        color: #64748b;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 5px;
    }
    .back-link:hover { color: #10b981; }
    .form-title {
        font-size: 1.4rem;
        font-weight: 800;
        color: #14532d;
    }

    /* =========================================
       PERBAIKAN TOTAL TABEL (FIX BLOK BIRU)
       ========================================= */
    .tabel-modern { 
        border-collapse: collapse; /* Kembalikan ke collapse agar rapi */
        width: 100%;
    }
    .tabel-modern thead th {
        background-color: #f8fafc !important;
        color: #475569 !important;
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 10px 12px !important;
        border-bottom: 2px solid #e2e8f0 !important;
        text-decoration: none !important; /* Hilangkan garis bawah */
        white-space: nowrap;
    }
    .tabel-modern tbody td {
        padding: 8px 12px !important;
        border-bottom: 1px solid #f1f5f9 !important;
        vertical-align: middle;
        background-color: #ffffff;
    }
    .btn-aksi-ikon {
        width: 28px; height: 28px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 6px; border: none;
        background: transparent; color: #ef4444;
        transition: all 0.2s;
    }
    .btn-aksi-ikon:hover { background: #fee2e2; }

    /* Input di dalam tabel agar terlihat jelas */
    #tabel-item .form-control, #tabel-penerima .form-control {
        background-color: transparent !important;
        border: 1px solid transparent !important;
        padding: 6px 10px !important;
        font-size: 0.85rem !important;
        color: #1e293b !important;
        box-shadow: none !important;
    }
    #tabel-item .form-control:hover, #tabel-penerima .form-control:hover {
        background-color: #f8fafc !important;
        border-color: #cbd5e1 !important;
    }
    #tabel-item .form-control:focus, #tabel-penerima .form-control:focus {
        background-color: #ffffff !important;
        border-color: #10b981 !important;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2) !important;
    }
    
    #tabel-item tbody tr:hover, #tabel-penerima tbody tr:hover {
        background-color: #f8fafc !important;
    }
    #tabel-item tfoot tr, #tabel-penerima tfoot tr {
        background-color: #f8fafc !important;
        border-top: 2px solid #10b981 !important;
    }

    /* Animasi */
    .animate-up {
        animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        opacity: 0;
        transform: translateY(10px);
    }
    @keyframes slideUp { to { opacity: 1; transform: translateY(0); } }
</style>
@endpush

@section('content')
    @php
        $penerimaAwal = collect(old('items', $spj->exists ? $spj->items->map(fn ($i) => [
            'uraian' => $i->uraian, 'jabatan' => $i->jabatan, 'harga_satuan' => \App\Support\Uang::isianHarga($i->jumlah),
            'nama_bank' => $i->nama_bank, 'no_rekening' => $i->no_rekening,
            'pajak' => (float) $i->pajak > 0 ? \App\Support\Uang::isianHarga($i->pajak) : '',
        ])->all() : []))->values()->all();
    @endphp

    {{-- Menggunakan container-fluid agar memanfaatkan lebar layar penuh --}}
    <div class="container-fluid py-3 animate-up">
        {{-- Tombol Kembali & Judul --}}
        <div class="mb-3">
            <a href="{{ $spj->exists ? route('spj.show', $spj) : route('spj.index') }}" class="back-link">
                <i class="bi bi-arrow-left"></i> {{ $spj->exists ? 'Kembali ke SPJ' : 'Daftar SPJ' }}
            </a>
            <h1 class="form-title mb-1">{{ $spj->exists ? 'Ubah SPJ ' . ($spj->nomor ?? '(draft)') : 'Buat SPJ Baru' }}</h1>
            <p class="text-muted small mb-0">Tahun anggaran {{ $tahun }}. Simpan sebagai draft, lalu ajukan jika sudah lengkap.</p>
        </div>

        @if (! $versi)
            <div class="alert alert-warning border-0 shadow-sm rounded-4">
                <strong>DPA tahun {{ $tahun }} belum diimpor.</strong> SPJ dibuat berdasarkan DPA.
            </div>
        @else
            @error('spj')<div class="alert alert-danger border-0 shadow-sm rounded-4" role="alert" style="white-space:pre-line">{{ $message }}</div>@enderror
            @if ($errors->any() && ! $errors->has('spj'))
                <div class="alert alert-danger border-0 shadow-sm rounded-4" role="alert">
                    <strong>Data belum lengkap.</strong>
                    <ul class="mb-0 mt-1">
                        @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" novalidate id="form-spj" action="{{ $spj->exists ? route('spj.update', $spj) : route('spj.store') }}">
                @csrf
                @if ($spj->exists) @method('PUT') @endif

                {{-- PANEL PAGU (DI ATAS) --}}
                <div class="form-card mb-3" style="border-left: 5px solid #10b981;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-wallet2"></i> Posisi Pagu Rekening
                        </h2>
                        <div id="kartu-kosong" class="text-secondary small">
                            <i class="bi bi-info-circle me-1"></i> Pilih rekening belanja terlebih dahulu.
                        </div>
                    </div>

                    <div id="kartu" hidden>
                        <div class="small text-muted fw-bold mb-2" id="kartu-nama"></div>
                        
                        {{-- Bar Chart --}}
                        <div class="rounded-pill overflow-hidden d-flex mb-3 shadow-sm" style="height:8px;background:#e2e8f0;">
                            <div id="bar-final" style="background:#10b981"></div>
                            <div id="bar-proses" style="background:#f59e0b"></div>
                            <div id="bar-ini" style="background:#14532d"></div>
                        </div>

                        <div class="row g-2 text-center text-md-start">
                            <div class="col-6 col-md-3">
                                <div class="small text-secondary fw-semibold mb-1" style="font-size:0.7rem">Pagu Tersedia</div>
                                <div class="fs-6 fw-bold text-dark" id="k-pagu"></div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="small text-secondary fw-semibold mb-1" style="font-size:0.7rem">Realisasi Final</div>
                                <div class="fs-6 fw-bold text-success" id="k-final"></div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="small text-secondary fw-semibold mb-1" style="font-size:0.7rem">SPJ Proses</div>
                                <div class="fs-6 fw-bold text-warning" id="k-proses"></div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="small text-secondary fw-semibold mb-1" style="font-size:0.7rem">Sisa Pagu</div>
                                <div class="fs-6 fw-bold text-primary" id="k-sisa"></div>
                            </div>
                            <div class="col-12 mt-2"><hr class="my-1 border-light"></div>
                            <div class="col-6 col-md-3">
                                <div class="small text-secondary fw-semibold mb-1" style="font-size:0.7rem">Nilai SPJ Ini</div>
                                <div class="fs-6 fw-bold text-dark" id="k-ini"></div>
                            </div>
                            <div class="col-6 col-md-9">
                                <div class="small text-secondary fw-semibold mb-1" style="font-size:0.7rem">Sisa Setelah SPJ Ini</div>
                                <div class="fs-6 fw-bold text-success" id="k-setelah"></div>
                            </div>
                        </div>
                        <div id="k-peringatan" class="alert alert-danger py-1 mt-2 mb-0 small border-0 rounded-2 shadow-sm" hidden><i class="bi bi-exclamation-triangle-fill me-1"></i> Nilai SPJ melebihi sisa pagu!</div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    {{-- KIRI: DASAR PEMBEBANAN --}}
                    <div class="col-12 col-lg-5">
                        <div class="form-card h-100">
                            <h2 class="mb-3 border-bottom pb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-info-circle-fill"></i> 1. Dasar Pembebanan
                            </h2>
                            <div class="row g-2">
                                <div class="col-12">
                                    <label for="jenis" class="form-label">Jenis SPJ</label>
                                    <select id="jenis" name="jenis" class="form-select">
                                        @foreach (\App\Models\Spj::JENIS as $k => $v)
                                            <option value="{{ $k }}" @selected(old('jenis', $spj->jenis) === $k)>{{ $v }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="kategori" class="form-label">Jenis Belanja</label>
                                    <select id="kategori" name="kategori" class="form-select @error('kategori') is-invalid @enderror">
                                        @foreach (\App\Models\Spj::KATEGORI as $k => $v)
                                            <option value="{{ $k }}" @selected(old('kategori', $spj->kategori ?? 'barang_jasa') === $k)>{{ $v['label'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label for="tanggal" class="form-label">Tanggal SPJ</label>
                                    <input id="tanggal" name="tanggal" type="date" value="{{ old('tanggal', $spj->tanggal?->format('Y-m-d')) }}" class="form-control @error('tanggal') is-invalid @enderror">
                                    @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label for="sub" class="form-label">Sub Kegiatan</label>
                                    <select id="sub" name="sub" class="form-select">
                                        <option value="">-- Pilih Sub Kegiatan --</option>
                                        @foreach ($sub as $s)
                                            <option value="{{ $s->id }}" @selected((string) old('sub', $subTerpilih) === (string) $s->id)>{{ $s->kode }} - {{ $s->uraian }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="anggaran_rekening_id" class="form-label">Rekening Belanja</label>
                                    <select id="anggaran_rekening_id" name="anggaran_rekening_id" class="form-select @error('anggaran_rekening_id') is-invalid @enderror" required>
                                        <option value="">-- Pilih Sub Kegiatan Dahulu --</option>
                                    </select>
                                    @error('anggaran_rekening_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label for="uraian" class="form-label">Uraian Pekerjaan</label>
                                    <textarea id="uraian" name="uraian" rows="2" class="form-control @error('uraian') is-invalid @enderror" placeholder="Tercetak sebagai isi pesanan pada nota pesanan...">{{ old('uraian', $spj->uraian) }}</textarea>
                                    @error('uraian')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- KANAN: PIHAK TERKAIT --}}
                    <div class="col-12 col-lg-7">
                        <div class="form-card h-100">
                            <h2 class="mb-3 border-bottom pb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-people-fill"></i> 3. Pihak Terkait
                            </h2>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label">Sumber Dana</label>
                                    <select id="sumber_dana_id" name="sumber_dana_id" class="form-select @error('sumber_dana_id') is-invalid @enderror">
                                        <option value="">Pilih Sumber Dana</option>
                                        @foreach ($sumberDana as $sd)
                                            <option value="{{ $sd->id }}" @selected((string) old('sumber_dana_id', $spj->sumber_dana_id) === (string) $sd->id)>{{ $sd->kode }}</option>
                                        @endforeach
                                    </select>
                                    @error('sumber_dana_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Penyedia (Rekanan)</label>
                                    <select id="penyedia_id" name="penyedia_id" class="form-select @error('penyedia_id') is-invalid @enderror">
                                        <option value="">Pilih Penyedia</option>
                                        @foreach ($penyedia as $p)
                                            <option value="{{ $p->id }}" @selected((string) old('penyedia_id', $spj->penyedia_id) === (string) $p->id)>{{ $p->nama }}</option>
                                        @endforeach
                                    </select>
                                    @error('penyedia_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <div class="form-text small" id="catatan-penyedia"></div>
                                </div>
                                @foreach ($pilihanPejabat as $kolom => $p)
                                    <div class="col-md-6">
                                        <label for="{{ $kolom }}" class="form-label">{{ $p['label'] }}</label>
                                        <select id="{{ $kolom }}" name="{{ $kolom }}" class="form-select @error($kolom) is-invalid @enderror">
                                            <option value="">Pilih</option>
                                            @foreach ($p['opsi'] as $o)
                                                <option value="{{ $o->id }}" @selected((string) old($kolom, $spj->{$kolom} ?? $p['default']) === (string) $o->id)>{{ $o->nama }}</option>
                                            @endforeach
                                        </select>
                                        @error($kolom)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FULL WIDTH: ITEM BELANJA --}}
                <div class="form-card mb-3" id="blok-item">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h2 class="mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-cart-fill"></i> 2. Item Belanja
                        </h2>
                        <button type="button" id="tambah-item" class="btn-premium py-1 px-3 text-sm shadow-sm">
                            <i class="bi bi-plus-lg me-1"></i> Tambah
                        </button>
                    </div>
                    <div class="table-responsive">
                        {{-- Menghapus kelas 'tabel-modern' yang mungkin konflik, langsung gunakan table murni --}}
                        <table class="table align-middle mb-0" id="tabel-item" style="width: 100%;">
                            <thead style="background-color: #f8fafc;">
                                <tr>
                                    <th style="min-width:250px; color:#475569; font-size:0.75rem; text-transform:uppercase; border-bottom:2px solid #e2e8f0; padding:10px;">URAIAN ITEM</th>
                                    <th style="width:100px; color:#475569; font-size:0.75rem; text-transform:uppercase; border-bottom:2px solid #e2e8f0; padding:10px; text-align:center;">VOL</th>
                                    <th style="width:120px; color:#475569; font-size:0.75rem; text-transform:uppercase; border-bottom:2px solid #e2e8f0; padding:10px; text-align:center;">SATUAN</th>
                                    <th style="width:180px; color:#475569; font-size:0.75rem; text-transform:uppercase; border-bottom:2px solid #e2e8f0; padding:10px;">HARGA (RP)</th>
                                    <th style="width:180px; color:#475569; font-size:0.75rem; text-transform:uppercase; border-bottom:2px solid #e2e8f0; padding:10px; text-align:right;">JUMLAH</th>
                                    <th style="width:60px; border-bottom:2px solid #e2e8f0;"></th>
                                </tr>
                            </thead>
                            <tbody id="item-baris"></tbody>
                            <tfoot>
                                <tr style="background-color: #f8fafc; border-top: 2px solid #10b981;">
                                    <th colspan="4" class="text-end py-3 fs-6" style="color:#475569;">TOTAL SPJ</th>
                                    <th class="text-end angka fs-5 text-primary py-3" id="total-spj">Rp0,00</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- DAFTAR PENERIMA (HONOR) --}}
                <div class="form-card mb-3" id="blok-penerima" hidden>
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 border-bottom pb-2">
                        <h2 class="mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-people"></i> 2. Daftar Penerima Honor
                        </h2>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('spj.penerima.templat') }}" class="btn-batal py-1 px-3 text-sm"><i class="bi bi-download me-1"></i> Templat</a>
                            <label class="btn-batal py-1 px-3 text-sm mb-0" for="impor-excel" style="cursor:pointer"><i class="bi bi-file-earmark-spreadsheet me-1"></i> Impor Excel</label>
                            <input type="file" id="impor-excel" accept=".xlsx,.xls" hidden>
                            <button type="button" id="tambah-penerima" class="btn-premium py-1 px-3 text-sm shadow-sm"><i class="bi bi-plus-lg me-1"></i> Tambah</button>
                        </div>
                    </div>
                    <div id="impor-info" class="alert py-2 small border-0 rounded-3" hidden></div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <label for="tarif-pajak" class="small fw-bold text-secondary mb-0">Tarif pajak</label>
                        <div class="input-group input-group-sm" style="width:120px">
                            <input type="text" id="tarif-pajak" class="form-control text-end" inputmode="decimal" placeholder="0">
                            <span class="input-group-text">%</span>
                        </div>
                        <button type="button" id="terapkan-tarif" class="btn-batal py-1 px-3 text-sm">Terapkan</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" id="tabel-penerima" style="width:100%;">
                            <thead style="background-color: #f8fafc;">
                                <tr>
                                    <th style="width:40px; color:#475569; font-size:0.75rem; border-bottom:2px solid #e2e8f0;">NO</th>
                                    <th style="min-width:170px; color:#475569; font-size:0.75rem; border-bottom:2px solid #e2e8f0;">NAMA PENERIMA</th>
                                    <th style="min-width:150px; color:#475569; font-size:0.75rem; border-bottom:2px solid #e2e8f0;">JABATAN</th>
                                    <th style="width:140px; color:#475569; font-size:0.75rem; border-bottom:2px solid #e2e8f0;">HONOR (RP)</th>
                                    <th style="width:120px; color:#475569; font-size:0.75rem; border-bottom:2px solid #e2e8f0;">NAMA BANK</th>
                                    <th style="width:140px; color:#475569; font-size:0.75rem; border-bottom:2px solid #e2e8f0;">NO. REK</th>
                                    <th style="width:130px; color:#475569; font-size:0.75rem; border-bottom:2px solid #e2e8f0;">PAJAK (RP)</th>
                                    <th style="width:140px; text-align:right; color:#475569; font-size:0.75rem; border-bottom:2px solid #e2e8f0;">DITERIMA</th>
                                    <th style="width:50px; border-bottom:2px solid #e2e8f0;"></th>
                                </tr>
                            </thead>
                            <tbody id="penerima-baris"></tbody>
                            <tfoot>
                                <tr style="background-color: #f8fafc; border-top: 2px solid #10b981;">
                                    <th colspan="3" class="text-end py-2">TOTAL</th>
                                    <th class="text-end angka py-2" id="pen-total-honor">Rp0,00</th>
                                    <th colspan="2"></th>
                                    <th class="text-end angka py-2" id="pen-total-pajak">Rp0,00</th>
                                    <th class="text-end angka text-primary py-2" id="pen-total-diterima">Rp0,00</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- TOMBOL SIMPAN --}}
                <div class="d-flex gap-2 pt-1 justify-content-end">
                    <a href="{{ $spj->exists ? route('spj.show', $spj) : route('spj.index') }}" class="btn-batal px-4 py-2">Batal</a>
                    <button type="submit" class="btn-premium px-5 py-2 shadow-sm">
                        <i class="bi bi-save me-2"></i> Simpan Draft
                    </button>
                </div>
            </form>
        @endif
    </div>
@endsection

@if ($versi)
    @push('scripts')
        <script>
            // =========================================
            // SCRIPT 1: LOGIKA ITEM BELANJA & PAGU
            // =========================================
            (function () {
                var URL_SUB = @json(url('/dpa/sub'));
                var AWAL_REK = @json((string) old('anggaran_rekening_id', $spj->anggaran_rekening_id));
                var ITEM_AWAL = @json(array_values($items));
                var elSub = document.getElementById('sub'), elRek = document.getElementById('anggaran_rekening_id');
                var rek = {}, nomor = 0, total = 0;

                function esc(t) { return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
                function rp(n) { return 'Rp' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n); }
                function harga(s) {
                    s = String(s || '').replace(/[^\d.,-]/g, '');
                    if (s.indexOf(',') > -1) s = s.replace(/\./g, '').replace(',', '.');
                    else if (/^\d{1,3}(\.\d{3})+$/.test(s)) s = s.replace(/\./g, '');
                    return parseFloat(s) || 0;
                }
                function volume(s) { return parseFloat(String(s || '').replace(',', '.')) || 0; }

                function kartu() {
                    var r = rek[elRek.value];
                    document.getElementById('kartu').hidden = !r;
                    document.getElementById('kartu-kosong').hidden = !!r;
                    if (!r) return;
                    var pagu = parseFloat(r.pagu), fin = parseFloat(r.realisasi), pro = parseFloat(r.reserved), sisa = pagu - fin - pro, setelah = sisa - total;

                    document.getElementById('kartu-nama').textContent = r.kode + ' ' + r.nama;
                    document.getElementById('k-pagu').textContent = rp(pagu);
                    document.getElementById('k-final').textContent = rp(fin);
                    document.getElementById('k-proses').textContent = rp(pro);
                    document.getElementById('k-sisa').textContent = rp(sisa);
                    document.getElementById('k-ini').textContent = rp(total);
                    document.getElementById('k-setelah').textContent = rp(setelah);

                    if (setelah < 0) {
                        document.getElementById('k-setelah').className = 'fs-6 fw-bold text-danger';
                        document.getElementById('k-peringatan').hidden = false;
                    } else {
                        document.getElementById('k-setelah').className = 'fs-6 fw-bold text-success';
                        document.getElementById('k-peringatan').hidden = true;
                    }

                    var p = function (x) { return pagu > 0 ? Math.max(0, Math.min(100, x / pagu * 100)) : 0; };
                    document.getElementById('bar-final').style.width = p(fin) + '%';
                    document.getElementById('bar-proses').style.width = p(pro) + '%';
                    document.getElementById('bar-ini').style.width = Math.min(p(total), 100 - p(fin) - p(pro)) + '%';
                }

                function muatRekening(subId, pilih) {
                    if (!subId) { elRek.innerHTML = '<option value="">Pilih sub kegiatan dulu</option>'; rek = {}; kartu(); return; }
                    elRek.innerHTML = '<option value="">Memuat...</option>';
                    fetch(URL_SUB + '/' + subId + '/rekening', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                        .then(function (r) { return r.json(); })
                        .then(function (rows) {
                            rek = {};
                            var h = '<option value="">Pilih rekening belanja</option>';
                            rows.forEach(function (r) { rek[r.id] = r; h += '<option value="' + r.id + '">' + esc(r.kode) + ' - ' + esc(r.nama) + '</option>'; });
                            elRek.innerHTML = h;
                            if (pilih) elRek.value = pilih;
                            kartu();
                        })
                        .catch(function () { elRek.innerHTML = '<option value="">Rekening gagal dimuat</option>'; });
                }

                function hitung() {
                    total = 0;
                    document.querySelectorAll('#item-baris tr').forEach(function (tr) {
                        var j = Math.round(harga(tr.querySelector('.i-harga').value) * volume(tr.querySelector('.i-volume').value) * 100) / 100;
                        tr.querySelector('.i-jumlah').textContent = rp(j);
                        total += j;
                    });
                    document.getElementById('total-spj').textContent = rp(total);
                    kartu();
                }

                function tambah(d) {
                    d = d || { uraian: '', volume: '1', satuan: '', harga_satuan: '' };
                    var i = nomor++, tr = document.createElement('tr');
                    tr.className = 'animate-up';
                    tr.style.animationDelay = (i * 0.05) + 's';
                    tr.innerHTML =
                        '<td><input name="items[' + i + '][uraian]" class="form-control form-control-sm fw-medium" value="' + esc(d.uraian) + '" required></td>' +
                        '<td><input name="items[' + i + '][volume]" class="form-control form-control-sm angka i-volume text-center" inputmode="decimal" value="' + esc(d.volume) + '" required></td>' +
                        '<td><input name="items[' + i + '][satuan]" class="form-control form-control-sm text-center" value="' + esc(d.satuan) + '" placeholder="-" required></td>' +
                        '<td><input name="items[' + i + '][harga_satuan]" class="form-control form-control-sm angka i-harga text-end" inputmode="decimal" value="' + esc(d.harga_satuan) + '" required></td>' +
                        '<td class="text-end angka fw-bold i-jumlah text-dark">Rp0,00</td>' +
                        '<td><button type="button" class="btn-aksi-ikon btn-hapus hapus" title="Hapus"><i class="bi bi-x-lg"></i></button></td>';
                    document.getElementById('item-baris').appendChild(tr);
                    hitung();
                }

                document.getElementById('tambah-item').addEventListener('click', function () { tambah(); });
                document.getElementById('item-baris').addEventListener('input', hitung);
                document.getElementById('item-baris').addEventListener('click', function (e) {
                    var b = e.target.closest('.hapus');
                    if (!b) return;
                    if (document.querySelectorAll('#item-baris tr').length > 1) {
                        b.closest('tr').remove();
                        hitung();
                    }
                });
                elSub.addEventListener('change', function () { muatRekening(elSub.value, null); });
                elRek.addEventListener('change', kartu);

                window.__spjSetTotal = function (t) { total = t; kartu(); };
                window.__spjHitungItem = hitung;

                ITEM_AWAL.forEach(tambah);
                if (elSub.value) muatRekening(elSub.value, AWAL_REK);
            })();

            // =========================================
            // SCRIPT 2: DAFTAR PENERIMA HONOR
            // =========================================
            (function () {
                var PENERIMA_AWAL = @json($penerimaAwal);
                var URL_IMPOR = @json(route('spj.penerima.impor'));
                var TOKEN = document.querySelector('#form-spj input[name=_token]').value;
                var elKategori = document.getElementById('kategori');
                var blokItem = document.getElementById('blok-item'), blokPen = document.getElementById('blok-penerima');
                var tbody = document.getElementById('penerima-baris'), nPen = 0;

                function esc(t) { return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
                function rp(n) { return 'Rp' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n); }
                function angka(s) {
                    s = String(s || '').replace(/[^\d.,-]/g, '');
                    if (s.indexOf(',') > -1) s = s.replace(/\./g, '').replace(',', '.');
                    else if (/^\d{1,3}(\.\d{3})+$/.test(s)) s = s.replace(/\./g, '');
                    return parseFloat(s) || 0;
                }
                function modeHonor() { return elKategori.value === 'honor_lembur'; }

                function hitungPenerima() {
                    var honor = 0, pajak = 0;
                    tbody.querySelectorAll('tr').forEach(function (tr, n) {
                        var h = angka(tr.querySelector('.p-honor').value), p = angka(tr.querySelector('.p-pajak').value);
                        tr.querySelector('.no-urut').textContent = n + 1;
                        tr.querySelector('.p-diterima').textContent = rp(h - p);
                        honor += h; pajak += p;
                    });
                    document.getElementById('pen-total-honor').textContent = rp(honor);
                    document.getElementById('pen-total-pajak').textContent = rp(pajak);
                    document.getElementById('pen-total-diterima').textContent = rp(honor - pajak);
                    if (modeHonor() && window.__spjSetTotal) window.__spjSetTotal(honor);
                }

                function tambahPenerima(d) {
                    d = d || {};
                    var i = 1000 + nPen++, tr = document.createElement('tr');
                    tr.innerHTML =
                        '<td class="text-center no-urut"></td>' +
                        '<td><input name="items[' + i + '][uraian]" class="form-control form-control-sm fw-medium" value="' + esc(d.uraian) + '" required></td>' +
                        '<td><input name="items[' + i + '][jabatan]" class="form-control form-control-sm" value="' + esc(d.jabatan) + '"></td>' +
                        '<td><input name="items[' + i + '][harga_satuan]" class="form-control form-control-sm angka p-honor text-end" inputmode="decimal" value="' + esc(d.harga_satuan) + '" required></td>' +
                        '<td><input name="items[' + i + '][nama_bank]" class="form-control form-control-sm" value="' + esc(d.nama_bank) + '"></td>' +
                        '<td><input name="items[' + i + '][no_rekening]" class="form-control form-control-sm angka" value="' + esc(d.no_rekening) + '"></td>' +
                        '<td><input name="items[' + i + '][pajak]" class="form-control form-control-sm angka p-pajak text-end" inputmode="decimal" value="' + esc(d.pajak) + '"></td>' +
                        '<td class="text-end angka fw-bold p-diterima">Rp0,00</td>' +
                        '<td><input type="hidden" name="items[' + i + '][volume]" value="1"><input type="hidden" name="items[' + i + '][satuan]" value="Orang">' +
                        '<button type="button" class="btn-aksi-ikon btn-hapus hapus-penerima" title="Hapus"><i class="bi bi-x-lg"></i></button></td>';
                    tbody.appendChild(tr);
                    hitungPenerima();
                }

                function barisKosong(tr) {
                    return ['uraian', 'jabatan', 'harga_satuan', 'nama_bank', 'no_rekening', 'pajak'].every(function (k) {
                        var el = tr.querySelector('[name$="[' + k + ']"]');
                        return !el || el.value.trim() === '';
                    });
                }

                function aturBlok() {
                    var h = modeHonor();
                    blokItem.hidden = h; blokPen.hidden = !h;
                    [[blokItem, h], [blokPen, !h]].forEach(function (p) {
                        p[0].querySelectorAll('input, select, textarea').forEach(function (e) { e.disabled = p[1]; });
                    });
                    document.getElementById('catatan-penyedia').textContent = h ? 'Tidak wajib untuk honorarium.' : '';
                    if (h) {
                        if (!tbody.children.length) { (PENERIMA_AWAL.length ? PENERIMA_AWAL : [{}]).forEach(tambahPenerima); }
                        hitungPenerima();
                    } else if (window.__spjHitungItem) {
                        window.__spjHitungItem();
                    }
                }

                function tampilInfo(jenis, teks) {
                    var el = document.getElementById('impor-info');
                    el.className = 'alert py-2 small border-0 rounded-3 alert-' + jenis;
                    el.innerHTML = teks;
                    el.hidden = false;
                }

                document.getElementById('impor-excel').addEventListener('change', function () {
                    var berkas = this.files[0], input = this;
                    if (!berkas) return;
                    var fd = new FormData();
                    fd.append('berkas', berkas); fd.append('_token', TOKEN);
                    tampilInfo('info', 'Membaca file...');
                    fetch(URL_IMPOR, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
                        .then(function (hasil) {
                            var j = hasil.j, galat = j.galat || [];
                            if (j.errors && j.errors.berkas) galat = j.errors.berkas;
                            var daftar = j.penerima || [];
                            if (daftar.length) {
                                tbody.querySelectorAll('tr').forEach(function (tr) { if (barisKosong(tr)) tr.remove(); });
                                var tarif = angka(document.getElementById('tarif-pajak').value);
                                daftar.forEach(function (d) {
                                    if (d.pajak === '' && tarif > 0) d.pajak = new Intl.NumberFormat('id-ID').format(Math.round(angka(d.harga_satuan) * tarif / 100));
                                    tambahPenerima(d);
                                });
                            }
                            var pesan = daftar.length ? '<strong>' + daftar.length + ' penerima</strong> berhasil dibaca dan ditambahkan ke daftar.' : '';
                            if (galat.length) {
                                pesan += (pesan ? '<br>' : '') + 'Perlu diperiksa:<ul class="mb-0">' + galat.map(function (g) { return '<li>' + esc(g) + '</li>'; }).join('') + '</ul>';
                            }
                            tampilInfo(galat.length ? 'warning' : 'success', pesan || 'Tidak ada data yang dibaca.');
                        })
                        .catch(function () { tampilInfo('danger', 'File gagal dibaca. Coba lagi atau gunakan templat.'); })
                        .finally(function () { input.value = ''; });
                });

                document.getElementById('terapkan-tarif').addEventListener('click', function () {
                    var tarif = angka(document.getElementById('tarif-pajak').value);
                    tbody.querySelectorAll('tr').forEach(function (tr) {
                        var h = angka(tr.querySelector('.p-honor').value);
                        tr.querySelector('.p-pajak').value = tarif > 0 && h > 0 ? new Intl.NumberFormat('id-ID').format(Math.round(h * tarif / 100)) : '';
                    });
                    hitungPenerima();
                });

                document.getElementById('tambah-penerima').addEventListener('click', function () { tambahPenerima(); });
                tbody.addEventListener('input', hitungPenerima);
                tbody.addEventListener('click', function (e) {
                    var b = e.target.closest('.hapus-penerima');
                    if (!b) return;
                    if (tbody.children.length > 1) { b.closest('tr').remove(); hitungPenerima(); }
                });
                elKategori.addEventListener('change', aturBlok);
                aturBlok();
            })();

            // =========================================
            // SCRIPT 3: VALIDASI FRONTEND (SWEETALERT)
            // =========================================
            document.getElementById('form-spj').addEventListener('submit', function(e) {
                var rekeningEl = document.getElementById('anggaran_rekening_id');

                if (!rekeningEl.value || rekeningEl.value.trim() === '') {
                    e.preventDefault();

                    Swal.fire({
                        icon: 'warning',
                        title: 'Rekening Belanja Kosong',
                        text: 'Silakan pilih Sub Kegiatan dan Rekening Belanja terlebih dahulu sebelum menyimpan draft.',
                        confirmButtonColor: '#059669',
                        confirmButtonText: 'Oke, Mengerti',
                        customClass: {
                            popup: 'rounded-4',
                            confirmButton: 'btn-premium'
                        },
                        buttonsStyling: false
                    });

                    rekeningEl.focus();
                }
            });
        </script>
    @endpush
@endif