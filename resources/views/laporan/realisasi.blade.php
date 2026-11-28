@extends('layouts.app')

@section('title', 'Realisasi anggaran')
@section('nav', 'realisasi')

@section('content')
    @php
        $parameter = array_merge($kueri, ['sampai' => $sampai->toDateString(), 'tahun' => $tahun, 'opd' => $opd?->id]);
        $n = fn ($sen) => number_format($sen / 100, 2, ',', '.');
    @endphp

    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1" style="color:var(--hutan)">Realisasi anggaran</h1>
            <p class="text-secondary mb-0" style="max-width:75ch">
                Pagu DPA aktif dibandingkan dengan SPJ final per program, kegiatan, sub kegiatan, dan rekening belanja. "Dalam proses" adalah SPJ yang sudah diajukan tetapi belum final.
            </p>
        </div>
        @if ($opd && $hasil && $hasil['versi'])
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('laporan.realisasi.excel', $parameter) }}" class="btn btn-outline-daun"><i class="bi bi-file-earmark-excel me-1"></i> Excel</a>
                <a href="{{ route('laporan.realisasi.pdf', $parameter) }}" target="_blank" rel="noopener" class="btn btn-kuning"><i class="bi bi-file-earmark-pdf me-1"></i> PDF</a>
            </div>
        @endif
    </div>

    @include('laporan._filter', ['aksi' => 'laporan.realisasi', 'mode' => 'sampai'])

    @if (! $opd)
        <section class="panel p-4 text-secondary">
            @if ($daftarOpd->isNotEmpty())
                Pilih OPD di atas untuk melihat laporannya.
            @else
                Akun Anda belum terhubung ke OPD. Hubungi admin untuk mengaturnya.
            @endif
        </section>
    @elseif (! $hasil['versi'])
        <section class="panel p-4">
            <strong>DPA tahun {{ $tahun }} untuk {{ $opd->nama }} belum diimpor.</strong>
            <span class="text-secondary">Laporan realisasi dibuat dari DPA aktif.</span>
        </section>
    @else
        @php $t = $hasil['total']; @endphp
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3"><div class="panel p-3 h-100"><div class="small text-secondary">Pagu</div><div class="fs-5 fw-bold angka">Rp{{ $n($t['pagu']) }}</div></div></div>
            <div class="col-6 col-lg-3"><div class="panel p-3 h-100"><div class="small text-secondary">Realisasi (final)</div><div class="fs-5 fw-bold angka">Rp{{ $n($t['final']) }}</div><div class="small text-secondary">{{ number_format($t['persen'], 2, ',', '.') }}% dari pagu</div></div></div>
            <div class="col-6 col-lg-3"><div class="panel p-3 h-100"><div class="small text-secondary">Dalam proses</div><div class="fs-5 fw-bold angka">Rp{{ $n($t['proses']) }}</div></div></div>
            <div class="col-6 col-lg-3"><div class="panel p-3 h-100"><div class="small text-secondary">Sisa pagu</div><div class="fs-5 fw-bold angka">Rp{{ $n($t['sisa']) }}</div></div></div>
        </div>

        <section class="panel p-3">
            <div class="d-flex flex-wrap gap-2 mb-2 px-1">
                <button type="button" class="btn btn-sm btn-light" id="buka-semua"><i class="bi bi-arrows-expand me-1"></i> Buka semua rekening</button>
                <button type="button" class="btn btn-sm btn-light" id="tutup-semua"><i class="bi bi-arrows-collapse me-1"></i> Tutup semua rekening</button>
                <span class="small text-secondary align-self-center">Klik tanda panah pada sub kegiatan untuk melihat rekening belanjanya.</span>
            </div>
            <div class="table-responsive">
                <table class="table tabel align-middle">
                    <thead>
                    <tr>
                        <th style="width:44px"></th><th>Kode</th><th>Uraian</th>
                        <th class="text-end">Pagu</th><th class="text-end">Realisasi</th><th class="text-end">Dalam proses</th><th class="text-end">Sisa pagu</th><th class="text-end" style="width:70px">%</th>
                    </tr>
                    </thead>
                    <tbody class="angka">
                    @foreach ($hasil['baris'] as $b)
                        <tr class="{{ $b['level'] === 'rekening' ? 'rek d-none' : '' }} {{ in_array($b['level'], ['program', 'kegiatan'], true) ? 'fw-bold' : '' }}"
                            @if ($b['level'] === 'rekening') data-induk="{{ $b['induk'] }}" @endif
                            @if ($b['level'] === 'program') style="background:#d9e8dc" @elseif ($b['level'] === 'kegiatan') style="background:#eaf2ec" @endif>
                            <td>
                                @if ($b['level'] === 'sub')
                                    <button type="button" class="btn btn-sm btn-light buka" data-kode="{{ $b['kode'] }}" aria-label="Lihat rekening belanja" aria-expanded="false"><i class="bi bi-chevron-right"></i></button>
                                @endif
                            </td>
                            <td class="{{ $b['level'] === 'sub' ? 'fw-semibold' : '' }}">{{ $b['kode'] }}</td>
                            <td class="{{ $b['level'] === 'sub' ? 'fw-semibold' : '' }}" style="padding-left: {{ ['program' => 8, 'kegiatan' => 20, 'sub' => 32, 'rekening' => 48][$b['level']] }}px">{{ $b['uraian'] }}</td>
                            <td class="text-end">{{ $n($b['pagu']) }}</td>
                            <td class="text-end">{{ $n($b['final']) }}</td>
                            <td class="text-end">{{ $n($b['proses']) }}</td>
                            <td class="text-end">{{ $n($b['sisa']) }}</td>
                            <td class="text-end">{{ number_format($b['persen'], 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr class="fw-bold">
                        <td colspan="3" class="text-end">TOTAL</td>
                        <td class="text-end angka">{{ $n($t['pagu']) }}</td>
                        <td class="text-end angka">{{ $n($t['final']) }}</td>
                        <td class="text-end angka">{{ $n($t['proses']) }}</td>
                        <td class="text-end angka">{{ $n($t['sisa']) }}</td>
                        <td class="text-end angka">{{ number_format($t['persen'], 2, ',', '.') }}</td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </section>
    @endif
@endsection

@if ($opd && $hasil && $hasil['versi'])
    @push('scripts')
        <script>
            (function () {
                function atur(kode, buka) {
                    document.querySelectorAll('tr.rek[data-induk="' + kode + '"]').forEach(function (tr) { tr.classList.toggle('d-none', !buka); });
                    var tombol = document.querySelector('button.buka[data-kode="' + kode + '"]');
                    if (tombol) {
                        tombol.setAttribute('aria-expanded', buka ? 'true' : 'false');
                        tombol.firstElementChild.className = buka ? 'bi bi-chevron-down' : 'bi bi-chevron-right';
                    }
                }
                document.addEventListener('click', function (e) {
                    var b = e.target.closest('button.buka');
                    if (b) atur(b.dataset.kode, b.getAttribute('aria-expanded') !== 'true');
                });
                document.getElementById('buka-semua').addEventListener('click', function () {
                    document.querySelectorAll('button.buka').forEach(function (b) { atur(b.dataset.kode, true); });
                });
                document.getElementById('tutup-semua').addEventListener('click', function () {
                    document.querySelectorAll('button.buka').forEach(function (b) { atur(b.dataset.kode, false); });
                });
            })();
        </script>
    @endpush
@endif