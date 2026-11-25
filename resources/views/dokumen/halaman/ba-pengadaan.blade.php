@php
    $s = $data['s'];
    $pp = $s['pejabat']['pejabat_pengadaan'];
    $sk = $s['sk']['pengadaan'];
    $np = $data['induk'];
@endphp
@include('dokumen._kop', ['data' => $data])
<div class="judul">BERITA ACARA PENGADAAN BARANG</div>
<div class="nomor">Nomor : {{ $data['nomor'] }}</div>

<table class="data">
    <tr><td class="k">Nama</td><td class="t">:</td><td>{{ $pp['nama'] ?: $data['titik'] }}</td></tr>
    <tr><td class="k">NIP</td><td class="t">:</td><td>{{ $pp['nip'] ?: $data['titik'] }}</td></tr>
    <tr><td class="k">Jabatan</td><td class="t">:</td><td>PEJABAT PENGADAAN BARANG</td></tr>
</table>

<p>Berdasarkan Surat Keputusan {{ $sk ? $sk['penerbit'] : $data['titik'] }} Nomor : {{ $sk ? $sk['nomor'] : $data['titik'] }}, Tanggal {{ $sk ? $sk['tanggal'] : $data['titik'] }} tentang Penunjukan Pejabat Pengadaan, Tahun {{ $s['anggaran']['tahun'] }}, sesuai dengan {{ $s['dasar_hukum'] }} telah mengadakan barang/jasa sesuai dengan ketentuan yang berlaku dan sesuai dengan Nota Pesanan/ SPK/ Surat Pesanan/ Kontrak :</p>

<table class="data">
    <tr><td class="k">Nomor</td><td class="t">:</td><td>{{ $np->nomor }}</td></tr>
    <tr><td class="k">Tanggal</td><td class="t">:</td><td>{{ $data['tanggal_panjang'] }}</td></tr>
</table>

<p>Dengan rincian sebagai berikut :</p>

@include('dokumen._tabel', ['data' => $data])

<table class="ttd">
    <tr>
        <td style="width:50%"><b>REKANAN</b></td>
        <td style="width:50%"><b>PEJABAT PENGADAAN</b></td>
    </tr>
    <tr>
        <td>
@include('dokumen._rekanan', ['data' => $data])
        </td>
        <td>
            <div class="ruang"></div>
@include('dokumen._orang', ['p' => $pp])
        </td>
    </tr>
</table>