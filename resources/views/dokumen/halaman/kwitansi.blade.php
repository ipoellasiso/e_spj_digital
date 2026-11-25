@php
    $s = $data['s'];
    $a = $s['anggaran'];
    $pa = $s['pejabat']['pengguna_anggaran'];
    $terimaDari = $pa['jabatan_struktural'] ?: ('Pengguna Anggaran ' . $s['opd']['nama']);
    $kategori = $s['kategori'] ?? 'barang_jasa';
    $lampiran = ['barang_jasa' => 'Invoice terlampir:', 'modal' => 'Invoice terlampir:', 'honor_lembur' => 'Daftar penerima terlampir.', 'gaji' => ''][$kategori] ?? '';
@endphp
@include('dokumen._kop', ['data' => $data])

<table class="data">
    <tr><td style="width:140px">Tahun</td><td class="t">:</td><td>{{ $a['tahun'] }}</td></tr>
    <tr><td>Sumber Dana</td><td class="t">:</td><td>{{ $a['sumber_dana'] }}</td></tr>
    <tr><td>Program</td><td class="t">:</td><td>{{ $a['program'] }}</td></tr>
    <tr><td>Kegiatan</td><td class="t">:</td><td>{{ $a['kegiatan'] }}</td></tr>
    <tr><td>Sub Kegiatan</td><td class="t">:</td><td>{{ $a['sub_kegiatan'] }}</td></tr>
    <tr><td>Rekening Belanja</td><td class="t">:</td><td>{{ $a['rekening_kode'] }}</td></tr>
</table>

<div class="kw-judul">TANDA PENERIMAAN</div>

<table class="data">
    <tr><td style="width:140px">Sudah Terima Dari</td><td class="t">:</td><td>{{ $terimaDari }}</td></tr>
    <tr><td>Terbilang</td><td class="t">:</td><td><i>{{ $data['terbilang'] }}</i></td></tr>
    <tr><td>Untuk Pembayaran</td><td class="t">:</td><td>{{ $data['uraian'] }}. Kegiatan {{ $a['kegiatan'] }}. {{ $lampiran }}</td></tr>
    <tr><td>Uang Sebesar</td><td class="t">:</td><td><span class="kw-kotak">{{ $data['total_rp'] }}</span></td></tr>
</table>

<table class="ttd" style="margin-top:22px">
    <tr>
        <td style="width:33%">Menyetujui,<br><b>Pejabat Pelaksana Teknis Kegiatan</b></td>
        <td style="width:33%"><br>Yang Membayarkan,<br><b>Bendahara Pengeluaran</b></td>
        <td style="width:34%">{{ $s['kota'] }}, {{ $data['tanggal_panjang'] }}<br><b>Yang Menerima</b></td>
    </tr>
    <tr>
        <td>
            <div class="ruang"></div>
@include('dokumen._orang', ['p' => $s['pejabat']['pptk']])
        </td>
        <td>
            <div class="ruang"></div>
@include('dokumen._orang', ['p' => $s['pejabat']['bendahara']])
        </td>
        <td>
@include('dokumen._rekanan', ['data' => $data])
@if ($kategori === 'honor_lembur')
            <div style="font-size:10pt"><i>(sesuai daftar penerima terlampir)</i></div>
@endif
        </td>
    </tr>
</table>

<table class="ttd" style="margin-top:12px">
    <tr><td>Mengetahui,<br><b>Pengguna Anggaran</b></td></tr>
    <tr>
        <td>
            <div class="ruang"></div>
@include('dokumen._orang', ['p' => $pa])
        </td>
    </tr>
</table>