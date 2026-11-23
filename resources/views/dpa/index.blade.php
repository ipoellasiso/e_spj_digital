@extends('layouts.app')

@section('title', 'DPA dan sisa pagu')
@section('nav', 'dpa')

@push('styles')
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endpush

@section('content')
    @php
        $rp = fn ($v) => 'Rp' . number_format((float) $v, 2, ',', '.');
    @endphp

    <div class="page-header">
        <div>
            <h1 class="page-title">DPA dan sisa pagu</h1>
            @if ($versi)
                <p class="page-desc">
                    {{ $opd->nama }}, tahun {{ $tahun }}. DPA {{ $versi->jenis }} versi {{ $versi->nomor_versi }}@if ($versi->nomor_dpa), {{ $versi->nomor_dpa }}@endif
                </p>
            @endif
        </div>
        @if ($bolehImpor)
            <div>
                <a href="{{ route('dpa.impor') }}" class="btn-premium">
                    <i class="bi bi-file-earmark-arrow-up"></i> Impor DPA dari Excel
                </a>
            </div>
        @endif
    </div>

    @if ($terbatas)
        <div class="alert alert-info border-0 shadow-sm rounded-4 mb-4" role="status">
            <i class="bi bi-info-circle-fill me-2"></i> Anda hanya melihat sub kegiatan yang ditugaskan kepada Anda.
        </div>
    @endif

    @if ($daftarOpd->isNotEmpty())
        <div class="card-modern p-4 mb-4">
            <form method="GET" class="d-flex flex-wrap align-items-end gap-3">
                <div style="min-width: 250px; flex: 1;">
                    <label for="opd" class="form-label small fw-bold text-secondary mb-1">OPD</label>
                    <select id="opd" name="opd" class="form-select" onchange="this.form.submit()">
                        <option value="">Pilih OPD</option>
                        @foreach ($daftarOpd as $o)
                            <option value="{{ $o->id }}" @selected($opd && $opd->id === $o->id)>{{ $o->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="width: 130px;">
                    <label for="tahun" class="form-label small fw-bold text-secondary mb-1">Tahun</label>
                    <input id="tahun" name="tahun" type="number" min="2000" max="2100" value="{{ $tahun }}" class="form-control">
                </div>
                <div>
                    <button class="btn-batal py-2 px-4" type="submit">Tampilkan</button>
                </div>
            </form>
        </div>
    @endif

    @if (! $opd)
        <div class="card-modern p-5 text-center text-secondary">
            <i class="bi bi-folder-x fs-1 mb-3 d-block opacity-50"></i>
            @if ($daftarOpd->isNotEmpty())
                Pilih OPD di atas untuk melihat DPA-nya.
            @else
                Akun Anda belum terhubung ke OPD. Hubungi admin untuk mengaturnya.
            @endif
        </div>
    @elseif (! $versi)
        <div class="card-modern p-5 text-center">
            <i class="bi bi-file-earmark-excel fs-1 text-warning mb-3 d-block"></i>
            <h5 class="fw-bold">DPA tahun {{ $tahun }} belum diimpor.</h5>
            <p class="text-secondary mb-4">Anda belum mengunggah file Excel untuk OPD {{ $opd->nama }} pada tahun ini.</p>
            @if ($bolehImpor)
                <a href="{{ route('dpa.impor') }}" class="btn-premium mx-auto">Impor DPA dari Excel</a>
            @else
                <p class="mb-0 text-muted small">Minta bendahara atau admin OPD untuk mengimpornya.</p>
            @endif
        </div>
    @else
        {{-- Panel Posisi Pagu --}}
        <div class="card-modern p-4 mb-4" style="background: linear-gradient(145deg, #ffffff, #f8fafc);">
            
            {{-- HEADER POSISI PAGU DENGAN DROPDOWN --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <h2 class="h6 fw-bold mb-0 text-dark">Posisi Pagu</h2>
                
                <div class="d-flex align-items-center gap-4">
                    {{-- DROPDOWN PILIH VERSI DPA --}}
                    @if(isset($daftarVersi) && $daftarVersi->count() > 1)
                        <div class="d-flex align-items-center gap-2">
                            <label for="pilih_versi" class="text-secondary small fw-bold mb-0 text-nowrap">Versi DPA:</label>
                            <select id="pilih_versi" class="form-select form-select-sm fw-semibold" style="width: auto; min-width: 180px; border-radius: 8px;" onchange="window.location.href = this.value;">
                                @foreach($daftarVersi as $v)
                                    @php
                                        // Bikin URL untuk pindah versi
                                        $urlVersi = route('dpa.index', [
                                            'opd' => $opd->id, 
                                            'tahun' => $tahun, 
                                            'versi_id' => $v->id
                                        ]);
                                        // Cek apakah ini versi yang sedang aktif dilihat
                                        $isSelected = $versi && $versi->id === $v->id;
                                    @endphp
                                    <option value="{{ $urlVersi }}" @selected($isSelected)>
                                        @if($v->jenis == 'murni')
                                            Murni (v{{ $v->nomor_versi }})
                                        @elseif($v->jenis == 'perubahan')
                                            Perubahan (v{{ $v->nomor_versi }})
                                        @else
                                            {{ ucfirst($v->jenis) }} (v{{ $v->nomor_versi }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="text-secondary border-start ps-4">
                        Pagu Total: <strong class="text-dark fs-5 ms-1 counter" data-target="{{ $total['pagu'] }}">Rp0</strong>
                    </div>
                </div>
            </div>
            
            {{-- Bar Chart Realisasi --}}
            <div class="d-flex rounded-pill overflow-hidden shadow-sm" style="height: 24px; background: #e2e8f0;" role="img">
                <div class="progress-bar-glow" style="width:{{ $total['persen_final'] }}%; background: linear-gradient(90deg, #059669, #10b981);" title="Final: {{ $total['persen_final'] }}%"></div>
                <div class="progress-bar-glow" style="width:{{ $total['persen_proses'] }}%; background: linear-gradient(90deg, #d97706, #fbbf24);" title="Proses: {{ $total['persen_proses'] }}%"></div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-12 col-md-4">
                    <div class="p-3 rounded-3 bg-white border border-light shadow-sm h-100">
                        <div class="d-flex align-items-center gap-2 text-secondary small fw-semibold mb-2">
                            <span class="rounded-circle" style="width:10px;height:10px;background:#10b981"></span> Realisasi Final
                        </div>
                        <div class="angka fs-4 fw-bold text-dark counter" data-target="{{ $total['final'] }}">Rp0</div>
                        <div class="text-success small mt-1 fw-medium"><i class="bi bi-arrow-up-right"></i> {{ $total['persen_final'] }}% dari pagu</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="p-3 rounded-3 bg-white border border-light shadow-sm h-100">
                        <div class="d-flex align-items-center gap-2 text-secondary small fw-semibold mb-2">
                            <span class="rounded-circle" style="width:10px;height:10px;background:#fbbf24"></span> SPJ Dalam Proses
                        </div>
                        <div class="angka fs-4 fw-bold text-dark counter" data-target="{{ $total['proses'] }}">Rp0</div>
                        <div class="text-warning small mt-1 fw-medium"><i class="bi bi-clock"></i> {{ $total['persen_proses'] }}% dari pagu</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="p-3 rounded-3 bg-white border border-light shadow-sm h-100">
                        <div class="d-flex align-items-center gap-2 text-secondary small fw-semibold mb-2">
                            <span class="rounded-circle" style="width:10px;height:10px;background:#cbd5e1"></span> Sisa Pagu
                        </div>
                        <div class="angka fs-4 fw-bold text-primary counter" data-target="{{ $total['sisa'] }}">Rp0</div>
                        <div class="text-secondary small mt-1 fw-medium"><i class="bi bi-wallet2"></i> {{ $total['persen_sisa'] }}% tersedia</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-tabel">
            <div class="p-3">
                <p class="text-secondary small px-1 mb-2"><i class="bi bi-info-circle me-1"></i> Klik tanda panah di awal baris untuk melihat rincian rekening belanja.</p>
                <table id="tabel" class="tabel tabel-modern w-100">
                    <thead>
                    <tr><th style="width:44px"></th><th>Kode</th><th>Sub kegiatan</th><th class="text-end">Pagu</th><th class="text-end">SPJ final</th><th class="text-end">Dalam proses</th><th class="text-end">Sisa</th></tr>
                    </thead>
                </table>
            </div>
        </div>
    @endif
@endsection

@if ($versi)
    @push('scripts')
        @include('partials.datatables-aset')
        <script>
            // --- ANIMASI COUNTER ANGKA ---
            document.addEventListener("DOMContentLoaded", function() {
                const counters = document.querySelectorAll('.counter');
                counters.forEach(counter => {
                    const target = +counter.getAttribute('data-target');
                    const duration = 1500; // 1.5 detik
                    const increment = target / (duration / 16); 
                    let current = 0;
                    const updateCounter = () => {
                        current += increment;
                        if (current < target) {
                            counter.innerText = 'Rp' + new Intl.NumberFormat('id-ID').format(Math.ceil(current));
                            requestAnimationFrame(updateCounter);
                        } else {
                            counter.innerText = 'Rp' + new Intl.NumberFormat('id-ID').format(target);
                        }
                    };
                    updateCounter();
                });
            });

            var URL_REKENING = @json(url('/dpa/sub'));

            function tabelRekening(rows) {
                if (!rows.length) return '<div class="p-3 text-secondary">Belum ada rekening.</div>';
                var h = '<div class="p-3 bg-light border-top border-bottom"><table class="table table-sm tabel-modern mb-0"><thead><tr><th>Rekening belanja</th><th class="text-end">Pagu</th><th class="text-end">SPJ final</th><th class="text-end">Dalam proses</th><th class="text-end">Sisa</th><th style="width:110px">Terpakai</th></tr></thead><tbody class="angka">';
                rows.forEach(function (r) {
                    var pagu = parseFloat(r.pagu), pakai = parseFloat(r.realisasi) + parseFloat(r.reserved);
                    var pct = pagu > 0 ? Math.min(100, pakai / pagu * 100) : 0;
                    var bar = pagu > 0
                        ? '<div class="rounded-pill overflow-hidden" style="height:6px;background:#e2e8f0"><div style="width:' + pct + '%;height:100%;background:' + (pct >= 90 ? '#ef4444' : '#10b981') + '"></div></div>'
                        : '<span class="text-secondary">-</span>';
                    h += '<tr><td><span class="badge bg-secondary mb-1">' + esc(r.kode) + '</span><br>' + esc(r.nama) + '</td>'
                       + '<td class="text-end">' + rupiah(r.pagu) + '</td><td class="text-end">' + rupiah(r.realisasi) + '</td>'
                       + '<td class="text-end">' + rupiah(r.reserved) + '</td><td class="text-end">' + rupiah(r.sisa) + '</td><td>' + bar + '</td></tr>';
                });
                return h + '</tbody></table></div>';
            }

            var tabel = $('#tabel').DataTable({
                processing: true, serverSide: true, language: DT_BAHASA,
                ajax: @json(route('dpa.data', request()->query())),
                order: [[1, 'asc']],
                dom: '<"tabel-toolbar d-flex justify-content-between align-items-center"lf>t<"tabel-footer d-flex justify-content-between align-items-center"ip>',
                classes: { sFilterInput: "form-control ms-2", sLengthSelect: "form-select" },
                columns: [
                    { data: null, orderable: false, searchable: false, defaultContent: '<button type="button" class="btn-aksi-ikon buka" aria-label="Lihat rekening belanja"><i class="bi bi-chevron-right"></i></button>' },
                    { data: 'kode', className: 'angka fw-semibold text-primary' },
                    { data: 'uraian', className: 'fw-medium' },
                    { data: 'pagu', className: 'text-end angka', searchable: false, render: rupiah },
                    { data: 'realisasi', className: 'text-end angka text-success', orderable: false, searchable: false, render: rupiah },
                    { data: 'reserved', className: 'text-end angka text-warning', orderable: false, searchable: false, render: rupiah },
                    { data: 'sisa', className: 'text-end angka fw-bold text-primary', orderable: false, searchable: false, render: rupiah }
                ]
            });

            $('#tabel tbody').on('click', 'button.buka', function () {
                var tr = $(this).closest('tr'), row = tabel.row(tr), ikon = $(this).find('i');
                if (row.child.isShown()) {
                    row.child.hide(); ikon.attr('class', 'bi bi-chevron-right'); return;
                }
                ikon.attr('class', 'bi bi-chevron-down');
                row.child('<div class="p-3 text-secondary"><div class="spinner-border spinner-border-sm me-2"></div>Memuat rekening...</div>').show();
                $.getJSON(URL_REKENING + '/' + row.data().id + '/rekening')
                    .done(function (rows) { row.child(tabelRekening(rows)).show(); })
                    .fail(function () { row.child('<div class="p-3 text-danger">Rekening gagal dimuat.</div>').show(); });
            });
        </script>
    @endpush
@endif