@php
    $s = $data['s'];
    $ppk = $s['pejabat']['pembuat_komitmen'];
    $pemeriksaan = $data['induk'];
@endphp
@include('dokumen._kop', ['data' => $data])
<div class="judul">BERITA ACARA PENYERAHAN BARANG</div>
<div class="nomor">Nomor : {{ $data['nomor'] }}</div>

@if ($pemeriksaan)
<p>Pada hari ini {{ $data['tanggal_kata'] }}, berdasarkan Berita Acara Pemeriksaan Barang :</p>

<table class="data">
    <tr><td class="k">Nomor</td><td class="t">:</td><td>{{ $pemeriksaan->nomor }}</td></tr>
    <tr><td class="k">Tanggal</td><td class="t">:</td><td>{{ \App\Support\TanggalIndo::panjang($pemeriksaan->tanggal) }}</td></tr>
</table>

<p>Dengan ini telah melakukan serah terima hasil pekerjaan sesuai dengan di bawah ini :</p>
@else
<p>Pada hari ini {{ $data['tanggal_kata'] }}, dengan ini telah melakukan serah terima barang hasil pengadaan sesuai dengan di bawah ini :</p>
@endif

@include('dokumen._tabel', ['data' => $data])

<p>Demikian berita acara ini dibuat untuk dipergunakan sebagaimana mestinya.</p>

<table class="ttd">
    <tr>
        <td style="width:50%"><b>REKANAN</b></td>
        <td style="width:50%"><b>Diterima Oleh,<br>PEJABAT PEMBUAT KOMITMEN</b></td>
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