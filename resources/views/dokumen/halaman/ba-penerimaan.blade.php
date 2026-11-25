@php
    $s = $data['s'];
    $pb = $s['pejabat']['pengurus_barang'];
    $ppk = $s['pejabat']['pembuat_komitmen'];
    $sk = $s['sk']['pengurus_barang'];
    $np = $data['induk'];
@endphp
@include('dokumen._kop', ['data' => $data])
<div class="judul">BERITA ACARA PENERIMAAN BARANG</div>
<div class="nomor">Nomor : {{ $data['nomor'] }}</div>

<p>Pada hari ini {{ $data['tanggal_kata'] }}, kami yang bertanda tangan di bawah ini :</p>

<table class="data">
    <tr><td class="k">Nama</td><td class="t">:</td><td>{{ $pb['nama'] ?: $data['titik'] }}</td></tr>
    <tr><td class="k">NIP</td><td class="t">:</td><td>{{ $pb['nip'] ?: $data['titik'] }}</td></tr>
    <tr><td class="k">Jabatan</td><td class="t">:</td><td>Pengurus Barang</td></tr>
</table>

<p>Berdasarkan keputusan {{ $sk ? $sk['penerbit'] : $data['titik'] }} Nomor : {{ $sk ? $sk['nomor'] : $data['titik'] }}, Tanggal {{ $sk ? $sk['tanggal'] : $data['titik'] }} sesuai dengan Surat Pesanan/SPK/Nota Pesanan Nomor : {{ $np->nomor }}, menyatakan telah menerima barang yang diserahkan oleh penyedia Barang/Jasa, dalam keadaan baik dan atau barang yang telah diadakan sesuai sebagaimana yang tercantum di bawah ini :</p>

@include('dokumen._tabel', ['data' => $data])

<table class="ttd">
    <tr>
        <td style="width:50%"><b>PEJABAT PEMBUAT KOMITMEN</b></td>
        <td style="width:50%"><b>PENGURUS BARANG</b></td>
    </tr>
    <tr>
        <td>
            <div class="ruang"></div>
@include('dokumen._orang', ['p' => $ppk])
        </td>
        <td>
            <div class="ruang"></div>
@include('dokumen._orang', ['p' => $pb])
        </td>
    </tr>
</table>