<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $meta['judul'] }}</title>
    <style>
        @page { margin: 1.4cm 1.6cm; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 8.5pt; color: #000; }
        .kepala { text-align: center; line-height: 1.35; }
        .kepala .pemda { font-weight: bold; font-size: 11pt; }
        .kepala .opd { font-weight: bold; font-size: 12pt; }
        .kepala .judul { font-weight: bold; font-size: 11pt; margin-top: 8px; }
        .garis { border: 0; border-top: 2px solid #000; margin: 6px 0 10px 0; }
        table.lap { width: 100%; border-collapse: collapse; }
        table.lap th, table.lap td { border: 1px solid #000; padding: 3px 5px; vertical-align: top; }
        table.lap th { background: #1f7a3f; color: #fff; text-align: center; }
        .tengah { text-align: center; }
        .kanan { text-align: right; }
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
                <th style="width:26px">No</th>
                <th style="width:62px">Tanggal</th>
                <th style="width:48px">No. SPJ</th>
                <th>Uraian</th>
                <th style="width:200px">Rekening Belanja</th>
                <th style="width:58px">Sumber Dana</th>
                <th style="width:92px">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
@foreach ($baris as $b)
            <tr>
                <td class="tengah">{{ $b['no'] }}</td>
                <td class="tengah">{{ $b['tanggal'] }}</td>
                <td class="tengah">{{ $b['nomor'] }}</td>
                <td>{{ $b['uraian'] }}</td>
                <td>{{ $b['rekening'] }}</td>
                <td class="tengah">{{ $b['sumber_dana'] }}</td>
                <td class="kanan">{{ number_format($b['jumlah_sen'] / 100, 2, ',', '.') }}</td>
            </tr>
@endforeach
@if (count($baris) === 0)
            <tr><td colspan="7" class="tengah">Tidak ada SPJ final pada periode ini.</td></tr>
@endif
            <tr class="total">
                <td colspan="6" class="kanan">TOTAL</td>
                <td class="kanan">{{ number_format($total_sen / 100, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

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