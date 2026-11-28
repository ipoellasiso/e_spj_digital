@php
    $s = $data['s'];
    $a = $s['anggaran'];
    $pa = $s['pejabat']['pengguna_anggaran'];
    $terimaDari = $pa['jabatan_struktural'] ?: ('Pengguna Anggaran ' . $s['opd']['nama']);
    $kategori = $s['kategori'] ?? 'barang_jasa';
    $lampiran = ['barang_jasa' => 'Invoice terlampir:', 'modal' => 'Invoice terlampir:', 'honor_lembur' => 'Daftar penerima terlampir.', 'gaji' => ''][$kategori] ?? '';
@endphp

@if ($kategori === 'gaji')
{{-- ===================== KWITANSI GAJI: tanpa kop, tanpa rekening, 2 penandatangan ===================== --}}
<div style="margin-top:30px">
    <table style="width:100%; border-collapse:collapse; font-size:11.5pt">
        <tr>
            <td style="width:150px; padding:6px 0; vertical-align:top"><b>Tahun</b></td>
            <td style="width:14px; padding:6px 0; vertical-align:top">:</td>
            <td style="padding:6px 0; vertical-align:top">{{ $a['tahun'] }}</td>
        </tr>
        <tr>
            <td style="padding:6px 0; vertical-align:top"><b>Sumber Dana</b></td>
            <td style="padding:6px 0; vertical-align:top">:</td>
            <td style="padding:6px 0; vertical-align:top">{{ $a['sumber_dana'] }}</td>
        </tr>
        <tr>
            <td style="padding:6px 0; vertical-align:top"><b>Program</b></td>
            <td style="padding:6px 0; vertical-align:top">:</td>
            <td style="padding:6px 0; vertical-align:top">{{ $a['program'] }}</td>
        </tr>
        <tr>
            <td style="padding:6px 0; vertical-align:top"><b>Kegiatan</b></td>
            <td style="padding:6px 0; vertical-align:top">:</td>
            <td style="padding:6px 0; vertical-align:top">{{ $a['kegiatan'] }}</td>
        </tr>
        <tr>
            <td style="padding:6px 0; vertical-align:top"><b>Sub Kegiatan</b></td>
            <td style="padding:6px 0; vertical-align:top">:</td>
            <td style="padding:6px 0; vertical-align:top">{{ $a['sub_kegiatan'] }}</td>
        </tr>
    </table>

    <div style="text-align:center; font-weight:bold; font-size:13pt; margin:34px 0 28px">TANDA PENERIMAAN</div>

    <table style="width:100%; border-collapse:collapse; font-size:11.5pt">
        <tr>
            <td style="width:150px; padding:10px 0; vertical-align:top"><b>Sudah Terima Dari</b></td>
            <td style="width:14px; padding:10px 0; vertical-align:top">:</td>
            <td style="padding:10px 0; vertical-align:top">{{ $terimaDari }}</td>
        </tr>
        <tr>
            <td style="padding:10px 0; vertical-align:top"><b>Terbilang</b></td>
            <td style="padding:10px 0; vertical-align:top">:</td>
            <td style="padding:10px 0; vertical-align:top"><b>{{ $data['terbilang'] }}</b></td>
        </tr>
        <tr>
            <td style="padding:10px 0; vertical-align:top"><b>Untuk Pembayaran</b></td>
            <td style="padding:10px 0; vertical-align:top">:</td>
            <td style="padding:10px 0; vertical-align:top">{{ $data['uraian'] }}</td>
        </tr>
        <tr>
            <td style="padding:10px 0; vertical-align:top"><b>Uang Sebesar</b></td>
            <td style="padding:10px 0; vertical-align:top">:</td>
            <td style="padding:10px 0; vertical-align:top"><b>{{ $data['total_rp'] }}</b></td>
        </tr>
    </table>

    <table class="ttd" style="margin-top:50px; width:100%">
        <tr>
            <td style="width:50%">Mengetahui/Menyetujui<br><b>Pengguna Anggaran</b></td>
            <td style="width:50%">{{ $s['kota'] }}, {{ $data['tanggal_panjang'] }}<br><b>Bendahara Pengeluaran</b></td>
        </tr>
        <tr>
            <td>
                <div class="ruang"></div>
@include('dokumen._orang', ['p' => $pa])
            </td>
            <td>
                <div class="ruang"></div>
@include('dokumen._orang', ['p' => $s['pejabat']['bendahara']])
            </td>
        </tr>
    </table>
</div>

@else
{{-- ===================== KATEGORI LAIN: tanpa kop, spasi lega ===================== --}}
<div style="margin-top:30px">
    <table style="width:100%; border-collapse:collapse; font-size:11.5pt">
        <tr>
            <td style="width:150px; padding:6px 0; vertical-align:top">Tahun</td>
            <td style="width:14px; padding:6px 0; vertical-align:top">:</td>
            <td style="padding:6px 0; vertical-align:top">{{ $a['tahun'] }}</td>
        </tr>
        <tr>
            <td style="padding:6px 0; vertical-align:top">Sumber Dana</td>
            <td style="padding:6px 0; vertical-align:top">:</td>
            <td style="padding:6px 0; vertical-align:top">{{ $a['sumber_dana'] }}</td>
        </tr>
        <tr>
            <td style="padding:6px 0; vertical-align:top">Program</td>
            <td style="padding:6px 0; vertical-align:top">:</td>
            <td style="padding:6px 0; vertical-align:top">{{ $a['program'] }}</td>
        </tr>
        <tr>
            <td style="padding:6px 0; vertical-align:top">Kegiatan</td>
            <td style="padding:6px 0; vertical-align:top">:</td>
            <td style="padding:6px 0; vertical-align:top">{{ $a['kegiatan'] }}</td>
        </tr>
        <tr>
            <td style="padding:6px 0; vertical-align:top">Sub Kegiatan</td>
            <td style="padding:6px 0; vertical-align:top">:</td>
            <td style="padding:6px 0; vertical-align:top">{{ $a['sub_kegiatan'] }}</td>
        </tr>
        <tr>
            <td style="padding:6px 0; vertical-align:top">Rekening Belanja</td>
            <td style="padding:6px 0; vertical-align:top">:</td>
            <td style="padding:6px 0; vertical-align:top">{{ $a['rekening_kode'] }}</td>
        </tr>
    </table>

    <div style="text-align:center; font-weight:bold; font-size:13pt; margin:34px 0 28px; text-decoration:underline">TANDA PENERIMAAN</div>

    <table style="width:100%; border-collapse:collapse; font-size:11.5pt">
        <tr>
            <td style="width:150px; padding:10px 0; vertical-align:top">Sudah Terima Dari</td>
            <td style="width:14px; padding:10px 0; vertical-align:top">:</td>
            <td style="padding:10px 0; vertical-align:top">{{ $terimaDari }}</td>
        </tr>
        <tr>
            <td style="padding:10px 0; vertical-align:top">Terbilang</td>
            <td style="padding:10px 0; vertical-align:top">:</td>
            <td style="padding:10px 0; vertical-align:top"><i>{{ $data['terbilang'] }}</i></td>
        </tr>
        <tr>
            <td style="padding:10px 0; vertical-align:top">Untuk Pembayaran</td>
            <td style="padding:10px 0; vertical-align:top">:</td>
            <td style="padding:10px 0; vertical-align:top">{{ $data['uraian'] }}. Kegiatan {{ $a['kegiatan'] }}. {{ $lampiran }}</td>
        </tr>
        <tr>
            <td style="padding:10px 0; vertical-align:middle">Uang Sebesar</td>
            <td style="padding:10px 0; vertical-align:middle">:</td>
            <td style="padding:10px 0; vertical-align:middle"><span class="kw-kotak">{{ $data['total_rp'] }}</span></td>
        </tr>
    </table>

    <table class="ttd" style="margin-top:44px; width:100%">
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

    <table class="ttd" style="margin-top:34px; width:100%">
        <tr><td>Mengetahui,<br><b>Pengguna Anggaran</b></td></tr>
        <tr>
            <td>
                <div class="ruang"></div>
@include('dokumen._orang', ['p' => $pa])
            </td>
        </tr>
    </table>
</div>
@endif