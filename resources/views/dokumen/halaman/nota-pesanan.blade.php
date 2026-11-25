@php
    $s = $data['s'];
    $ppk = $s['pejabat']['pembuat_komitmen'];
    $nomorDpa = $s['anggaran']['nomor_dpa'] ?: $data['titik'];
@endphp
@include('dokumen._kop', ['data' => $data])
<div class="judul">NOTA PESANAN</div>
<div class="nomor">Nomor : {{ $data['nomor'] }}</div>

<table class="data">
    <tr><td class="k">Dari</td><td class="t">:</td><td>{{ $ppk['nama'] ?: $data['titik'] }}</td></tr>
    <tr><td class="k">Jabatan</td><td class="t">:</td><td>{{ $ppk['jabatan_struktural'] ?: $ppk['jabatan'] }}</td></tr>
    <tr><td class="k">Kepada</td><td class="t">:</td><td>{{ $s['penyedia']['nama'] ?: $data['titik'] }}{{ $s['penyedia']['alamat'] ? ', ' . $s['penyedia']['alamat'] : '' }}</td></tr>
    <tr><td class="k">Dasar</td><td class="t">:</td><td>Nomor {{ $nomorDpa }}</td></tr>
    <tr><td class="k">Tanggal</td><td class="t">:</td><td>{{ $data['tanggal_panjang'] }}</td></tr>
    <tr><td class="k"><b>Isi Pesanan</b></td><td class="t">:</td><td>{{ $data['uraian'] }}. Kegiatan {{ $s['anggaran']['kegiatan'] }}. Invoice terlampir:</td></tr>
</table>

@include('dokumen._tabel', ['data' => $data])

<table class="ttd">
    <tr>
        <td style="width:50%"><br><b>REKANAN</b></td>
        <td style="width:50%">{{ $s['kota'] }}, {{ $data['tanggal_panjang'] }}<br><b>PEJABAT PEMBUAT KOMITMEN</b></td>
    </tr>
    <tr>
        <td>
@include('dokumen._rekanan', ['data' => $data])
        </td>
        <td>
            <div class="ruang"></div>
@include('dokumen._orang', ['p' => $ppk])
        </td>
    </tr>
</table>