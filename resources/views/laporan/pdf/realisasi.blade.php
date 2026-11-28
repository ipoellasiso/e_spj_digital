<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $meta['judul'] }}</title>
    <style>
        @page { margin: 1.4cm 1.6cm; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 8pt; color: #000; }
        .kepala { text-align: center; line-height: 1.35; }
        .kepala .pemda { font-weight: bold; font-size: 11pt; }
        .kepala .opd { font-weight: bold; font-size: 12pt; }
        .kepala .judul { font-weight: bold; font-size: 11pt; margin-top: 8px; }
        .garis { border: 0; border-top: 2px solid #000; margin: 6px 0 10px 0; }
        table.lap { width: 100%; border-collapse: collapse; }
        table.lap th, table.lap td { border: 1px solid #000; padding: 3px 5px; vertical-align: top; }
        table.lap th { background: #1f7a3f; color: #fff; text-align: center; }
        .kanan { text-align: right; }
        .l-program td { background: #d9e8dc; font-weight: bold; }
        .l-kegiatan td { background: #eaf2ec; font-weight: bold; }
        .l-sub td { font-weight: bold; }
        .total td { font-weight: bold; background: #eeeeee; }
        table.ttd { width: 100%; margin-top: 22px; page-break-inside: avoid; }
        table.ttd td { text-align: center; vertical-align: top; width: 50%; }
        .nama { font-weight: bold; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="kepala">
        <div class="pemda">{{ $meta['pemda'] }}</div>
        <div class="opd">{{ $meta['opd'] }}</div>
        <div class="judul">{{ $meta['judul'] }}</div>
        <div>{{ $meta['periode'] }}</div>
    </div>
    <hr class="garis">

    <table class="lap">
        <thead>
            <tr>
                <th style="width:105px">Kode</th>
                <th>Uraian</th>
                <th style="width:88px">Pagu (Rp)</th>
                <th style="width:88px">Realisasi (Rp)</th>
                <th style="width:88px">Dalam Proses (Rp)</th>
                <th style="width:88px">Sisa Pagu (Rp)</th>
                <th style="width:44px">%</th>
            </tr>
        </thead>
        <tbody>
@foreach ($baris as $b)
            <tr class="l-{{ $b['level'] }}">
                <td>{{ $b['kode'] }}</td>
                <td style="padding-left: {{ ['program' => 5, 'kegiatan' => 14, 'sub' => 24, 'rekening' => 36][$b['level']] }}px">{{ $b['uraian'] }}</td>
                <td class="kanan">{{ number_format($b['pagu'] / 100, 2, ',', '.') }}</td>
                <td class="kanan">{{ number_format($b['final'] / 100, 2, ',', '.') }}</td>
                <td class="kanan">{{ number_format($b['proses'] / 100, 2, ',', '.') }}</td>
                <td class="kanan">{{ number_format($b['sisa'] / 100, 2, ',', '.') }}</td>
                <td class="kanan">{{ number_format($b['persen'], 2, ',', '.') }}</td>
            </tr>
@endforeach
            <tr class="total">
                <td colspan="2" class="kanan">TOTAL</td>
                <td class="kanan">{{ number_format($total['pagu'] / 100, 2, ',', '.') }}</td>
                <td class="kanan">{{ number_format($total['final'] / 100, 2, ',', '.') }}</td>
                <td class="kanan">{{ number_format($total['proses'] / 100, 2, ',', '.') }}</td>
                <td class="kanan">{{ number_format($total['sisa'] / 100, 2, ',', '.') }}</td>
                <td class="kanan">{{ number_format($total['persen'], 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    <p style="font-size:7.5pt; margin-top:6px">Realisasi adalah SPJ berstatus final. "Dalam proses" adalah SPJ yang sudah diajukan tetapi belum final pada saat laporan dicetak.</p>

    <table class="ttd">
        <tr>
            <td>Mengetahui,<br>Pengguna Anggaran</td>
            <td>{{ config('spj.kota') }}, {{ $tanggal_cetak }}<br>Bendahara Pengeluaran</td>
        </tr>
        <tr>
            <td style="padding-top:56px">
@if ($ttd['pa']['nama'] !== '')
                <div class="nama">{{ $ttd['pa']['nama'] }}</div>
                <div>NIP. {{ $ttd['pa']['nip'] }}</div>
@else
                <div>..............................</div>
@endif
            </td>
            <td style="padding-top:56px">
@if ($ttd['bendahara']['nama'] !== '')
                <div class="nama">{{ $ttd['bendahara']['nama'] }}</div>
                <div>NIP. {{ $ttd['bendahara']['nip'] }}</div>
@else
                <div>..............................</div>
@endif
            </td>
        </tr>
    </table>
</body>
</html>