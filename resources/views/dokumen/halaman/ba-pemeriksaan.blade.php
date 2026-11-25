@php
    $s = $data['s'];
    $ppk = $s['pejabat']['pembuat_komitmen'];
    $sk = $s['sk']['ppk'];
    $np = $data['induk'];
    $baik = $data['d']->hasil_pemeriksaan !== 'kurang_baik';
@endphp
@include('dokumen._kop', ['data' => $data])
<div class="judul">BERITA ACARA PEMERIKSAAN BARANG</div>
<div class="nomor">Nomor : {{ $data['nomor'] }}</div>

<p>Pada hari ini {{ $data['tanggal_panjang'] }} bertempat di {{ $s['opd']['nama'] }} berdasarkan Keputusan {{ $sk ? $sk['penerbit'] : $data['titik'] }} Nomor: {{ $sk ? $sk['nomor'] : $data['titik'] }}, tanggal {{ $sk ? $sk['tanggal'] : $data['titik'] }}, yang bertanda tangan di bawah ini :</p>

<table class="data">
    <tr><td class="k">Nama</td><td class="t">:</td><td>{{ $ppk['nama'] ?: $data['titik'] }}</td></tr>
    <tr><td class="k">NIP</td><td class="t">:</td><td>{{ $ppk['nip'] ?: $data['titik'] }}</td></tr>
    <tr><td class="k">Jabatan</td><td class="t">:</td><td>PEJABAT PEMBUAT KOMITMEN</td></tr>
</table>

<p>Berdasarkan surat permintaan dari penyedia tentang penyerahan hasil pekerjaan/ barang sesuai dengan Nota Pesanan / SPK / Surat Pesanan / Kontrak :</p>

<table class="data">
    <tr><td class="k">Nomor</td><td class="t">:</td><td>{{ $np->nomor }}</td></tr>
    <tr><td class="k">Tanggal</td><td class="t">:</td><td>{{ \App\Support\TanggalIndo::panjang($np->tanggal) }}</td></tr>
</table>

<p>Telah melakukan pemeriksaan dengan hasil sebagai berikut :</p>

@include('dokumen._tabel', ['data' => $data])

<p>Hasil pemeriksaan dinyatakan :</p>
<table class="data">
    <tr><td style="width:30px">1.</td><td style="width:40px">[ {!! $baik ? 'X' : '&nbsp;' !!} ]</td><td>Baik</td></tr>
    <tr><td>2.</td><td>[ {!! $baik ? '&nbsp;' : 'X' !!} ]</td><td>Kurang Baik</td></tr>
</table>

<p>Demikian berita acara ini dibuat untuk dipergunakan sebagaimana mestinya.</p>

<table class="ttd">
    <tr>
        <td style="width:50%"><b>REKANAN</b></td>
        <td style="width:50%"><b>YANG MEMERIKSA,<br>PEJABAT PEMBUAT KOMITMEN</b></td>
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