@extends('layouts.app')

@section('title', 'SPJ ' . ($spj->nomor ?? 'draft'))
@section('nav', 'spj')

@push('styles')
<style>
    :root {
        --hutan: #14532d;
        --daun: #166534;
        --kuning: #f59e0b;
        --krem: #fefce8;
    }

    /* ===== Animasi masuk ===== */
    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes popIn {
        0%   { opacity: 0; transform: scale(.92); }
        60%  { transform: scale(1.02); }
        100% { opacity: 1; transform: scale(1); }
    }
    @keyframes shimmer {
        0%   { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }
    @keyframes pulseRing {
        0%   { box-shadow: 0 0 0 0 rgba(245, 158, 11, .45); }
        70%  { box-shadow: 0 0 0 10px rgba(245, 158, 11, 0); }
        100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
    }

    .anim-in        { animation: fadeSlideUp .5s cubic-bezier(.22,.61,.36,1) both; }
    .anim-in.d1     { animation-delay: .06s; }
    .anim-in.d2     { animation-delay: .12s; }
    .anim-in.d3     { animation-delay: .18s; }
    .anim-in.d4     { animation-delay: .24s; }
    .anim-in.d5     { animation-delay: .30s; }
    .anim-pop       { animation: popIn .45s cubic-bezier(.22,.61,.36,1) both; }

    /* ===== Header hero ===== */
    .spj-hero {
        position: relative;
        background: linear-gradient(135deg, #14532d 0%, #166534 55%, #15803d 100%);
        color: #f0fdf4;
        border-radius: 18px;
        padding: 1.75rem 1.75rem;
        overflow: hidden;
        box-shadow: 0 18px 38px -22px rgba(20, 83, 45, .65);
    }
    .spj-hero::before {
        content: "";
        position: absolute; inset: 0;
        background:
            radial-gradient(circle at 85% 20%, rgba(245,158,11,.28), transparent 45%),
            radial-gradient(circle at 10% 90%, rgba(255,255,255,.10), transparent 40%);
        pointer-events: none;
    }
    .spj-hero::after {
        content: "";
        position: absolute; top: 0; left: -60%;
        width: 60%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.08), transparent);
        animation: shimmer 6s linear infinite;
        pointer-events: none;
    }
    .spj-hero h1 { position: relative; z-index: 1; }
    .spj-hero .uraian { position: relative; z-index: 1; color: #bbf7d0; }

    .spj-hero .st {
        background: rgba(255,255,255,.16) !important;
        color: #f0fdf4 !important;
        border: 1px solid rgba(255,255,255,.28);
        backdrop-filter: blur(4px);
        padding: .35rem .7rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 600;
    }

    /* ===== Panel ===== */
    .panel {
        background: #fff;
        border: 1px solid #e7e5e4;
        border-radius: 14px;
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    }
    .panel:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 30px -22px rgba(20, 83, 45, .5);
        border-color: #d6d3d1;
    }
    .panel-kepala {
        padding: .9rem 1.1rem;
        border-bottom: 1px solid #f1f0ee;
        background: linear-gradient(180deg, #fafaf9, #fff);
        border-radius: 14px 14px 0 0;
    }
    .panel-kepala h2 {
        font-size: .95rem; font-weight: 700; margin: 0;
        color: var(--hutan);
        display: flex; align-items: center; gap: .5rem;
    }
    .panel-kepala h2::before {
        content: "";
        width: 6px; height: 18px; border-radius: 3px;
        background: linear-gradient(180deg, var(--kuning), var(--daun));
    }

    /* ===== Tabel keren ===== */
    .tabel-keren {
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }
    .tabel-keren thead th {
        background: linear-gradient(180deg, #14532d, #166534);
        color: #ecfdf5;
        font-size: .74rem;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
        padding: .75rem .85rem;
        border: none !important;
        white-space: nowrap;
    }
    .tabel-keren thead th:first-child { border-radius: 10px 0 0 0; }
    .tabel-keren thead th:last-child  { border-radius: 0 10px 0 0; }

    .tabel-keren tbody td {
        padding: .7rem .85rem;
        border-color: #f1f0ee !important;
        vertical-align: middle;
        font-size: .875rem;
        transition: background .18s ease, padding-left .18s ease;
    }
    .tabel-keren tbody tr { transition: background .18s ease; }
    .tabel-keren tbody tr:nth-child(even) { background: #fafaf9; }
    .tabel-keren tbody tr:hover {
        background: #fef9e7;
    }
    .tabel-keren tbody tr:hover td:first-child {
        padding-left: 1.05rem;
        color: var(--daun);
        font-weight: 700;
    }
    .tabel-keren tbody tr:hover td {
        border-color: #fde68a !important;
    }

    .tabel-keren tfoot th {
        background: linear-gradient(180deg, #fffbeb, #fef3c7);
        color: #78350f;
        font-weight: 700;
        padding: .8rem .85rem;
        border-color: #fde68a !important;
        font-size: .875rem;
    }

    /* Nomor urut badge */
    .no-badge {
        display: inline-flex; align-items: center; justify-content: center;
        width: 24px; height: 24px;
        border-radius: 8px;
        background: #ecfdf5;
        color: var(--daun);
        font-size: .75rem; font-weight: 700;
        transition: all .18s ease;
    }
    .tabel-keren tbody tr:hover .no-badge {
        background: var(--daun);
        color: #fff;
        transform: scale(1.08) rotate(-4deg);
    }

    /* ===== Tombol ===== */
    .btn-kuning {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: #fff; border: none;
        transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
    }
    .btn-kuning:hover {
        transform: translateY(-1px);
        filter: brightness(1.05);
        box-shadow: 0 10px 20px -10px rgba(217, 119, 6, .7);
        color: #fff;
    }
    .btn-outline-daun {
        border: 1px solid var(--daun);
        color: var(--daun);
        background: #fff;
        transition: all .18s ease;
    }
    .btn-outline-daun:hover {
        background: var(--daun);
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 10px 20px -12px rgba(22, 101, 52, .6);
    }

    /* ===== Kartu info (grid detail) ===== */
    .info-grid { display: grid; gap: .75rem; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }
    .info-item {
        background: #fafaf9;
        border: 1px solid #f1f0ee;
        border-radius: 10px;
        padding: .7rem .85rem;
        transition: all .2s ease;
    }
    .info-item:hover {
        background: #f0fdf4;
        border-color: #bbf7d0;
        transform: translateY(-1px);
    }
    .info-item .label {
        font-size: .72rem; color: #78716c;
        text-transform: uppercase; letter-spacing: .04em;
        margin-bottom: .15rem;
    }
    .info-item .value { font-weight: 600; color: #1c1917; font-size: .9rem; }
    .info-item.span-2 { grid-column: span 2; }

    /* ===== Posisi pagu ===== */
    .pagu-row {
        display: flex; justify-content: space-between;
        padding: .5rem 0;
        border-bottom: 1px dashed #e7e5e4;
        font-size: .85rem;
    }
    .pagu-row:last-child { border-bottom: none; }
    .pagu-row.total {
        background: linear-gradient(90deg, #f0fdf4, #ecfdf5);
        margin: .5rem -.75rem -.25rem;
        padding: .6rem .75rem;
        border-radius: 8px;
        border-bottom: none;
        font-weight: 700;
        color: var(--hutan);
    }
    .pagu-row.over { color: #b91c1c; }

    /* ===== Riwayat timeline ===== */
    .timeline-item {
        position: relative;
        padding-left: 1.5rem;
        padding-bottom: .9rem;
    }
    .timeline-item::before {
        content: "";
        position: absolute; left: 5px; top: 6px;
        width: 10px; height: 10px; border-radius: 50%;
        background: var(--daun);
        box-shadow: 0 0 0 3px #dcfce7;
        animation: popIn .4s ease both;
    }
    .timeline-item::after {
        content: "";
        position: absolute; left: 9px; top: 20px; bottom: 0;
        width: 2px; background: #e7e5e4;
    }
    .timeline-item:last-child::after { display: none; }
    .timeline-item:last-child::before {
        background: var(--kuning);
        box-shadow: 0 0 0 3px #fef3c7;
        animation: popIn .4s ease both, pulseRing 2s ease-in-out infinite;
    }

    /* ===== List dokumen ===== */
    .dok-item {
        display: flex; justify-content: space-between; align-items: center;
        padding: .7rem .5rem;
        border-radius: 8px;
        text-decoration: none;
        color: #1c1917;
        transition: all .18s ease;
        border-bottom: 1px solid #f1f0ee;
    }
    .dok-item:last-child { border-bottom: none; }
    .dok-item:hover {
        background: #f0fdf4;
        padding-left: .85rem;
        color: var(--hutan);
    }
    .dok-item i { transition: transform .18s ease; }
    .dok-item:hover i { transform: translateX(3px) rotate(-5deg); color: var(--daun); }

    /* ===== Alert ===== */
    .alert { border-radius: 10px; border-width: 1px; }

    /* ===== SweetAlert2 Custom Theme ===== */
    .swal2-popup {
        border-radius: 18px !important;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        box-shadow: 0 25px 50px -12px rgba(20, 83, 45, 0.4) !important;
    }
    .swal2-title {
        color: #14532d !important;
        font-weight: 800 !important;
        font-size: 1.35rem !important;
    }
    .swal2-html-container {
        font-size: 0.95rem !important;
        color: #57534e !important;
    }

    /* Reduced motion */
    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: .001ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .001ms !important;
        }
    }
</style>
@endpush

@section('content')
    @php
        $rp = fn ($v) => \App\Support\Uang::rupiah(\App\Support\Uang::sen($v));
        $ar = $spj->anggaranRekening;
        $setelah = $sisaSen - ($spj->status->bisaDiubah() ? \App\Support\Uang::sen($spj->total) : 0);
        $tindakan = app(\App\Services\SpjWorkflowService::class)->tindakanTersedia($spj, auth()->user());
        $aksiLabel = ['diajukan' => 'Diajukan', 'diverifikasi' => 'Diverifikasi', 'disetujui' => 'Disetujui', 'dikembalikan' => 'Dikembalikan', 'dibatalkan' => 'Dibatalkan'];
        $tahapLabel = ['pengajuan' => '', 'bendahara' => 'oleh bendahara', 'ppk_skpd' => 'oleh PPK-SKPD', 'pa' => 'oleh pengguna anggaran', 'pembatalan' => ''];
    @endphp

    <div class="mb-3 anim-in">
        <a href="{{ route('spj.index') }}" class="small text-decoration-none">
            <i class="bi bi-arrow-left"></i> Daftar SPJ
        </a>
    </div>

    {{-- HERO HEADER --}}
    <div class="spj-hero anim-in mb-4">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
            <div>
                <h1 class="h4 fw-bold mb-2">
                    SPJ {{ $spj->nomor ?? '(belum bernomor)' }}
                    <span class="st ms-2 align-middle">{{ $spj->status->label() }}</span>
                </h1>
                <p class="uraian mb-0" style="max-width:70ch">{{ $spj->uraian }}</p>
            </div>

            @if ($bolehKelola)
                <div class="d-flex flex-wrap gap-2 position-relative" style="z-index:2">
                    <a href="{{ route('spj.edit', $spj) }}" class="btn btn-outline-daun bg-white">
                        <i class="bi bi-pencil me-1"></i> Ubah
                    </a>
                    
                    <form method="POST" action="{{ route('spj.ajukan', $spj) }}" id="formAjukan">
                        @csrf
                        <button type="button" class="btn btn-kuning" onclick="konfirmasiAjukan()">
                            <i class="bi bi-send me-1"></i> Ajukan
                        </button>
                    </form>
                    
                    @if ($spj->status === \App\Enums\SpjStatus::Draft && $spj->nomor_urut === null)
                        <form method="POST" action="{{ route('spj.destroy', $spj) }}" id="formHapus">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-light text-danger" onclick="konfirmasiHapus()">Hapus</button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>

    @error('ajukan')
        <div class="alert alert-danger anim-pop" role="alert">
            <strong>SPJ belum bisa diajukan.</strong>
            <div style="white-space:pre-line" class="mt-1">{{ $message }}</div>
        </div>
    @enderror
    @error('spj')<div class="alert alert-danger anim-pop" role="alert" style="white-space:pre-line">{{ $message }}</div>@enderror
    @error('proses')<div class="alert alert-danger anim-pop" role="alert">{{ $message }}</div>@enderror

    @if ($tindakan['maju'] || $tindakan['kembalikan'] || $tindakan['batalkan'] || $tindakan['tertahan'])
        <section class="panel p-4 mb-4 anim-in d1" style="border-left:4px solid var(--kuning)">
            <h2 class="h6 fw-bold mb-2" style="color:var(--hutan)">
                {{ $spj->status === \App\Enums\SpjStatus::Final ? 'Pembatalan SPJ final' : 'Menunggu tindakan Anda' }}
            </h2>
            @if ($tindakan['tertahan'])
                <div class="alert alert-warning py-2 small" role="status">{{ $tindakan['tertahan'] }}</div>
            @endif
            <form method="POST" action="{{ route('spj.proses', $spj) }}" id="formProses">
                @csrf
                <label for="catatan" class="form-label fw-semibold small">
                    Catatan <span class="text-secondary fw-normal">(wajib bila mengembalikan atau membatalkan)</span>
                </label>
                <textarea id="catatan" name="catatan" rows="2" maxlength="1000" class="form-control mb-3"
                          placeholder="Tulis apa yang perlu diperbaiki, atau alasan pembatalan"></textarea>
                <div class="d-flex flex-wrap gap-2">
                    @if ($tindakan['maju'])
                        <button type="button" class="btn btn-kuning" onclick="konfirmasiProses('maju', '{{ $tindakan['maju'] }} SPJ ini?', false)">
                            <i class="bi bi-check2-circle me-1"></i> {{ $tindakan['maju'] }}
                        </button>
                    @endif
                    @if ($tindakan['kembalikan'])
                        <button type="button" class="btn btn-outline-danger" onclick="konfirmasiProses('kembalikan', 'Kembalikan SPJ ini ke pembuatnya? Pagu yang ditahan akan dilepas.', true)">
                            <i class="bi bi-arrow-return-left me-1"></i> Kembalikan
                        </button>
                    @endif
                    @if ($tindakan['batalkan'])
                        <button type="button" class="btn btn-light text-danger" onclick="konfirmasiProses('batalkan', 'Batalkan SPJ ini? Tindakan ini tidak bisa diurungkan.', true)">
                            <i class="bi bi-x-octagon me-1"></i> Batalkan SPJ
                        </button>
                    @endif
                </div>
            </form>
        </section>
    @endif

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <section class="panel p-4 mb-4 anim-in d2">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="label">Tanggal</div>
                        <div class="value angka">{{ $spj->tanggal->format('d/m/Y') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Jenis</div>
                        <div class="value">{{ \App\Models\Spj::JENIS[$spj->jenis] ?? $spj->jenis }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Jenis belanja</div>
                        <div class="value">{{ $spj->kategoriInfo()['label'] }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Sumber dana</div>
                        <div class="value">{{ $spj->sumberDana?->kode }}</div>
                    </div>
                    <div class="info-item span-2">
                        <div class="label">Sub kegiatan</div>
                        <div class="value"><span class="angka">{{ $ar->subKegiatan->kode }}</span> {{ $ar->subKegiatan->uraian }}</div>
                    </div>
                    {{-- <div class="info-item span-2">
                        <div class="label">Rekening belanja</div>
                        <div class="value"><span class="angka">{{ $ar->rekening->kode }}</span> {{ $ar->rekening->nama }}</div>
                    </div> --}}
                    <div class="info-item span-2">
                        <div class="label">Rekening belanja</div>
                        @if ($spj->kategori === 'gaji')
                            <div class="value">{{ $spj->items->count() }} rekening (lihat item belanja)</div>
                        @else
                            <div class="value"><span class="angka">{{ $ar->rekening->kode }}</span> {{ $ar->rekening->nama }}</div>
                        @endif
                    </div>
                    <div class="info-item">
                        <div class="label">Penyedia</div>
                        <div class="value">{{ $spj->penyedia?->nama ?? '-' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Dibuat oleh</div>
                        <div class="value">{{ $spj->pembuat?->name ?? '-' }}</div>
                    </div>
                </div>
            </section>

            @if ($spj->kategori === 'honor_lembur')
            <section class="panel mb-4 anim-in d3">
                <div class="panel-kepala"><h2>Daftar penerima honor</h2></div>
                <div class="table-responsive">
                    <table class="table tabel-keren">
                        <thead>
                            <tr>
                                <th>No</th><th>Nama</th><th>Jabatan</th>
                                <th class="text-end">Honor</th><th>Bank dan rekening</th>
                                <th class="text-end">Pajak</th><th class="text-end">Diterima</th>
                            </tr>
                        </thead>
                        <tbody class="angka">
                        @foreach ($spj->items as $i => $it)
                            <tr>
                                <td><span class="no-badge">{{ $i + 1 }}</span></td>
                                <td>{{ $it->uraian }}</td>
                                <td>{{ $it->jabatan ?: '-' }}</td>
                                <td class="text-end">{{ $rp($it->jumlah) }}</td>
                                <td>{{ trim(($it->nama_bank ?? '') . ' ' . ($it->no_rekening ?? '')) ?: '-' }}</td>
                                <td class="text-end">{{ $rp($it->pajak) }}</td>
                                <td class="text-end">{{ \App\Support\Uang::rupiah(\App\Support\Uang::sen($it->jumlah) - \App\Support\Uang::sen($it->pajak)) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total</th>
                                <th class="text-end angka">{{ $rp($spj->total) }}</th>
                                <th></th>
                                <th class="text-end angka">{{ $rp($spj->items->sum('pajak')) }}</th>
                                <th class="text-end angka">{{ \App\Support\Uang::rupiah(\App\Support\Uang::sen($spj->total) - \App\Support\Uang::sen($spj->items->sum('pajak'))) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
            @elseif ($spj->kategori === 'gaji')
            <section class="panel mb-4 anim-in d3">
                <div class="panel-kepala"><h2>Item belanja gaji</h2></div>
                <div class="table-responsive">
                    <table class="table tabel-keren">
                        <thead><tr><th>No</th><th>Rekening belanja</th><th class="text-end">Jumlah</th></tr></thead>
                        <tbody class="angka">
                        @foreach ($spj->items as $i => $it)
                            <tr>
                                <td><span class="no-badge">{{ $i + 1 }}</span></td>
                                <td>{{ $it->uraian }}</td>
                                <td class="text-end">{{ $rp($it->jumlah) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr><th colspan="2" class="text-end">Total</th><th class="text-end angka">{{ $rp($spj->total) }}</th></tr>
                        </tfoot>
                    </table>
                </div>
            </section>
            @else
            <section class="panel mb-4 anim-in d3">
                <div class="panel-kepala"><h2>Item belanja</h2></div>
                <div class="table-responsive">
                    <table class="table tabel-keren">
                        <thead>
                            <tr>
                                <th>No</th><th>Uraian</th><th class="text-end">Volume</th>
                                <th>Satuan</th><th class="text-end">Harga satuan</th><th class="text-end">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="angka">
                        @foreach ($spj->items as $i => $it)
                            <tr>
                                <td><span class="no-badge">{{ $i + 1 }}</span></td>
                                <td>{{ $it->uraian }}</td>
                                <td class="text-end">{{ \App\Support\Uang::isianVolume($it->volume) }}</td>
                                <td>{{ $it->satuan }}</td>
                                <td class="text-end">{{ $rp($it->harga_satuan) }}</td>
                                <td class="text-end">{{ $rp($it->jumlah) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-end">Total</th>
                                <th class="text-end angka">{{ $rp($spj->total) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
            @endif

            <section class="panel p-4 anim-in d4">
                <h2 class="h6 fw-bold mb-3" style="color:var(--hutan)">Pejabat penandatangan</h2>
                <div class="info-grid">
                    @foreach (\App\Models\Spj::PEJABAT as $kolom => [$label])
                        <div class="info-item">
                            <div class="label">{{ $label }}</div>
                            <div class="value">{{ $pejabat[$spj->{$kolom}]->nama ?? '-' }}</div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="col-12 col-xl-4">
            @php
                $dok = app(\App\Services\DokumenSpjService::class)->ringkasan($spj);
            @endphp
            @if ($dok['tersedia'])
                <section class="panel p-4 mb-4 anim-in d2">
                    <h2 class="h6 fw-bold mb-3" style="color:var(--hutan)">Dokumen cetak</h2>
                    @foreach ($dok['peringatan'] as $w)
                        <div class="alert alert-warning py-2 small">{{ $w }}</div>
                    @endforeach
                    <div class="mb-3">
                        @foreach ($dok['dokumen'] as $d)
                            <a href="{{ route('spj.dokumen', [$spj, $d['slug']]) }}" target="_blank" rel="noopener"
                               class="dok-item">
                                <span>
                                    {{ $d['judul'] }}
                                    <span class="d-block small text-secondary angka">{{ $d['nomor'] }}</span>
                                </span>
                                <i class="bi bi-printer"></i>
                            </a>
                        @endforeach
                    </div>
                    @if ($dok['bisa_paket'])
                        <a href="{{ route('spj.dokumen.semua', $spj) }}" target="_blank" rel="noopener" class="btn btn-kuning w-100 mb-2">
                            <i class="bi bi-printer me-1"></i> Cetak semua (satu PDF)
                        </a>
                        <a href="{{ route('spj.dokumen.semua', [$spj, 'unduh' => 1]) }}" class="btn btn-outline-daun w-100">
                            <i class="bi bi-download me-1"></i> Unduh PDF
                        </a>
                    @elseif (count($dok['dokumen']) > 1)
                        <div class="small text-secondary">Daftar penerima dicetak pada kertas lanskap, jadi dokumen dicetak satu per satu.</div>
                    @endif
                    @if ($dok['hasil_pemeriksaan'] !== null && $spj->status !== \App\Enums\SpjStatus::Final && auth()->user()->hasAnyRole(['bendahara', 'pptk', 'ppk_skpd']))
                        <form method="POST" action="{{ route('spj.dokumen.pemeriksaan', $spj) }}" class="mt-3 pt-3 border-top">
                            @csrf
                            <label for="hasil" class="form-label small fw-semibold">Hasil pemeriksaan barang (BA Pemeriksaan)</label>
                            <div class="d-flex gap-2">
                                <select id="hasil" name="hasil" class="form-select form-select-sm">
                                    <option value="baik" @selected($dok['hasil_pemeriksaan'] === 'baik')>Baik</option>
                                    <option value="kurang_baik" @selected($dok['hasil_pemeriksaan'] === 'kurang_baik')>Kurang baik</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-outline-daun">Simpan</button>
                            </div>
                        </form>
                    @endif
                </section>
            @endif

            @if ($paguGaji)
                <section class="panel p-4 mb-4 anim-in d3">
                    <h2 class="h6 fw-bold mb-3" style="color:var(--hutan)">Posisi pagu per rekening</h2>
                    <div class="angka small">
                        @foreach ($paguGaji as $p)
                            @php $setelahR = $p['sisa'] - ($spj->status->bisaDiubah() ? $p['ini'] : 0); @endphp
                            <div class="pagu-row {{ $setelahR < 0 ? 'over' : '' }}">
                                <span>{{ $p['kode'] }}</span>
                                <span>sisa {{ \App\Support\Uang::rupiah($setelahR) }}</span>
                            </div>
                        @endforeach
                    </div>
                    @if ($spj->status->bisaDiubah() && collect($paguGaji)->contains(fn ($p) => $p['sisa'] - $p['ini'] < 0))
                        <div class="alert alert-danger py-2 mt-3 mb-0 small">Ada rekening yang melebihi sisa pagu. SPJ ini tidak bisa diajukan.</div>
                    @endif
                </section>
                @else
                <section class="panel p-4 mb-4 anim-in d3">
                    <h2 class="h6 fw-bold mb-3" style="color:var(--hutan)">Posisi pagu rekening</h2>
                    <div class="angka">
                        <div class="pagu-row">
                            <span class="text-secondary">Pagu</span>
                            <span>{{ $rp($ar->pagu) }}</span>
                        </div>
                        <div class="pagu-row">
                            <span class="text-secondary">SPJ final</span>
                            <span>{{ $rp($ar->realisasi_final) }}</span>
                        </div>
                        <div class="pagu-row">
                            <span class="text-secondary">Dalam proses</span>
                            <span>{{ $rp($ar->reserved) }}</span>
                        </div>
                        <div class="pagu-row total">
                            <span>Sisa pagu</span>
                            <span>{{ \App\Support\Uang::rupiah($sisaSen) }}</span>
                        </div>
                        @if ($spj->status->bisaDiubah())
                            <div class="pagu-row mt-2">
                                <span class="text-secondary">SPJ ini</span>
                                <span>{{ $rp($spj->total) }}</span>
                            </div>
                            <div class="pagu-row {{ $setelah < 0 ? 'over' : '' }} fw-semibold">
                                <span>Sisa setelah diajukan</span>
                                <span>{{ \App\Support\Uang::rupiah($setelah) }}</span>
                            </div>
                        @endif
                    </div>
                    @if ($spj->status->bisaDiubah() && $setelah < 0)
                        <div class="alert alert-danger py-2 mt-3 mb-0 small">Melebihi sisa pagu. SPJ ini tidak bisa diajukan.</div>
                    @endif
                </section>
            @endif

            <section class="panel p-4 anim-in d4">
                <h2 class="h6 fw-bold mb-3" style="color:var(--hutan)">Riwayat</h2>
                @forelse ($spj->approval as $a)
                    <div class="timeline-item">
                        <div class="fw-semibold" style="font-size:.9rem">
                            {{ trim(($aksiLabel[$a->aksi] ?? ucfirst($a->aksi)) . ' ' . ($tahapLabel[$a->tahap] ?? '')) }}
                        </div>
                        <div class="small text-secondary">{{ $a->user?->name ?? '-' }}, {{ $a->created_at->format('d/m/Y H:i') }}</div>
                        @if ($a->catatan)<div class="small mt-1">{{ $a->catatan }}</div>@endif
                    </div>
                @empty
                    <div class="text-secondary small">Belum ada riwayat. SPJ masih draft.</div>
                @endforelse
            </section>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // --- Konfigurasi Dasar SweetAlert ---
    const swalTheme = {
        customClass: {
            popup: 'shadow-lg',
            confirmButton: 'btn btn-kuning',
            cancelButton: 'btn btn-light'
        },
        buttonsStyling: false,
        showClass: {
            popup: 'animate__animated animate__fadeInUp animate__faster'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutDown animate__faster'
        }
    };

    // --- 1. Konfirmasi Ajukan SPJ ---
    function konfirmasiAjukan() {
        Swal.fire({
            ...swalTheme,
            title: 'Ajukan SPJ?',
            text: 'Pagu rekening akan ditahan dan SPJ tidak bisa diubah lagi.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-send me-1"></i> Ya, Ajukan',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            iconColor: '#f59e0b'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formAjukan').submit();
            }
        });
    }

    // --- 2. Konfirmasi Hapus Draft ---
    function konfirmasiHapus() {
        Swal.fire({
            ...swalTheme,
            title: 'Hapus Draft?',
            text: 'Draft SPJ ini akan dihapus permanen. Tindakan ini tidak dapat diurungkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-trash me-1"></i> Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            iconColor: '#b91c1c',
            customClass: {
                confirmButton: 'btn btn-danger', // Tombol merah untuk aksi hapus
                cancelButton: 'btn btn-light'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formHapus').submit();
            }
        });
    }

    // --- 3. Konfirmasi Proses Workflow (Verifikasi / Kembalikan / Batalkan) ---
    function konfirmasiProses(aksi, pesan, isDanger = false) {
        const form = document.getElementById('formProses');
        
        // Siapkan hidden input untuk mengirim 'aksi'
        const existing = form.querySelector('input[name="aksi"]');
        if (existing) existing.remove();
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'aksi';
        actionInput.value = aksi;
        form.appendChild(actionInput);

        // Konfigurasi SweetAlert berdasarkan jenis aksi
        const config = {
            ...swalTheme,
            title: aksi === 'maju' ? 'Konfirmasi Verifikasi' : (aksi === 'kembalikan' ? 'Konfirmasi Pengembalian' : 'Konfirmasi Pembatalan'),
            text: pesan,
            icon: isDanger ? 'warning' : 'info',
            showCancelButton: true,
            confirmButtonText: isDanger ? '<i class="bi bi-exclamation-triangle me-1"></i> Ya, Lanjutkan' : '<i class="bi bi-check2-circle me-1"></i> Ya, Verifikasi',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            iconColor: isDanger ? '#b91c1c' : '#14532d'
        };

        // Ubah warna tombol jika aksi berbahaya
        if (isDanger) {
            config.customClass = {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-light'
            };
        }

        Swal.fire(config).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }
</script>
@endpush