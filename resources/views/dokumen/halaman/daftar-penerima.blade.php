@php
    $s = $data['s'];
    $pa = $s['pejabat']['pengguna_anggaran'];
    $pptk = $s['pejabat']['pptk'];
@endphp
<div style="font-family: Helvetica, Arial, sans-serif; font-size: 9pt;">
    <div style="text-align:center; font-weight:bold; font-size:11pt; margin-top:8px;">DAFTAR PENERIMA HONOR / INSENTIF</div>
    <div style="text-align:center; font-weight:bold; margin:8px 0 22px 0;">Tahun Anggaran {{ $s['anggaran']['tahun'] }}</div>

    <table class="dp">
        <tr>
            <th style="width:28px">No</th>
            <th style="width:120px">Nama Penerima</th>
            <th style="width:130px">Jabatan</th>
            <th style="width:80px">Honor (Rp)</th>
            <th style="width:85px">Nama Bank</th>
            <th style="width:90px">No. Rek</th>
            <th style="width:85px">Nilai Pajak (Rp)</th>
            <th style="width:85px">Diterima (Rp)</th>
            <th style="width:50px">TTD</th>
        </tr>
@foreach ($data['penerima'] as $p)
        <tr>
            <td style="text-align:center">{{ $p['no'] }}</td>
            <td>{{ $p['nama'] }}</td>
            <td>{{ $p['jabatan'] }}</td>
            <td style="text-align:right">{{ $p['honor'] }}</td>
            <td>{{ $p['bank'] }}</td>
            <td>{{ $p['rekening'] }}</td>
            <td style="text-align:right">{{ $p['pajak'] }}</td>
            <td style="text-align:right">{{ $p['diterima'] }}</td>
            <td style="height:22px">&nbsp;</td>
        </tr>
@endforeach
        <tr style="background:#eeeeee; font-weight:bold">
            <td colspan="3" style="text-align:right">TOTAL</td>
            <td style="text-align:right">{{ $data['total'] }}</td>
            <td></td>
            <td></td>
            <td style="text-align:right">{{ $data['total_pajak'] }}</td>
            <td style="text-align:right">{{ $data['total_diterima'] }}</td>
            <td></td>
        </tr>
    </table>

    <p style="margin-top:12px; text-align:left"><b>Terbilang:</b> {{ $data['terbilang_diterima'] }}</p>

    <table style="width:100%; margin-top:30px; page-break-inside:avoid; font-family: Helvetica, Arial, sans-serif; font-size:9pt;">
        <tr>
            <td style="width:50%; text-align:center; vertical-align:top">Mengetahui,<br>Pengguna Anggaran</td>
            <td style="width:50%; text-align:center; vertical-align:top">{{ $s['kota'] }}, {{ $data['tanggal_panjang'] }}<br>Pejabat Pelaksana Teknis Kegiatan</td>
        </tr>
        <tr>
            <td style="text-align:center; padding-top:62px">
@include('dokumen._orang', ['p' => $pa])
            </td>
            <td style="text-align:center; padding-top:62px">
@include('dokumen._orang', ['p' => $pptk])
            </td>
        </tr>
    </table>
</div>