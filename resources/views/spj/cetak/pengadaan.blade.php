<div class="page">
    @include('spj.cetak.kop')
    <div class="judul-dokumen">BERITA ACARA PENGADAAN BARANG</div>
    <div class="text-center mb-4">Nomor : {{ $spj->nomor }}/BAPP/{{ $spj->singkatan_opd }}/{{ $spj->tanggal->format('M') }}/{{ $spj->tahun }}</div>
    
    <table class="tabel-data">
        <tr><td width="20%">Nama</td><td>: {{ $pejabat[$spj->pejabat_pengadaan_id]->nama ?? '-' }}</td></tr>
        <tr><td>NIP</td><td>: {{ $pejabat[$spj->pejabat_pengadaan_id]->nip ?? '-' }}</td></tr>
        <tr><td>Jabatan</td><td>: Pejabat Pengadaan Barang</td></tr>
    </table>

    <p>Berdasarkan Surat Keputusan Kepala {{ $opd->nama }} Nomor : SK/2026, tentang Penunjukan Pejabat Pengadaan Tahun {{ $spj->tahun }}, sesuai dengan Peraturan Presiden Nomor 16 Tahun 2018 tentang Pengadaan Barang/Jasa Pemerintah, telah mengadakan barang/jasa sesuai dengan ketentuan yang berlaku dan sesuai dengan Nota Pesanan/SPK/Kontrak :</p>
    <p>Nomor : {{ $spj->nomor }}/NP/{{ $spj->singkatan_opd }}/{{ $spj->tanggal->format('M') }}/{{ $spj->tahun }}<br>
    Tanggal : {{ $spj->tanggal->translatedFormat('d F Y') }}</p>
    
    <p>Dengan rincian sebagai berikut :</p>
    <table class="tabel-item">
        <thead>
            <tr><th width="5%">No</th><th>Nama Barang</th><th>Volume</th><th>Satuan</th><th>Harga</th><th>Jumlah</th></tr>
        </thead>
        <tbody>
            @foreach($spj->items as $i => $item)
            <tr>
                <td class="text-center">{{ $i+1 }}</td>
                <td>{{ $item->uraian }}</td>
                <td class="text-center">{{ \App\Support\Uang::isianVolume($item->volume) }}</td>
                <td class="text-center">{{ $item->satuan }}</td>
                <td class="text-end">{{ \App\Support\Uang::rupiah($item->harga_satuan) }}</td>
                <td class="text-end">{{ \App\Support\Uang::rupiah($item->jumlah) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><th colspan="5" class="text-end">Total</th><th class="text-end">{{ \App\Support\Uang::rupiah($spj->total) }}</th></tr>
            <tr><td colspan="6" class="fst-italic">Terbilang: {{ ucwords(terbilang($spj->total)) }} Rupiah</td></tr>
        </tfoot>
    </table>
    
    <div class="area-ttd mt-5 d-flex justify-content-end">
        <div class="kotak-ttd">
            <div>Pejabat Pengadaan,</div>
            <div class="ruang-ttd"></div>
            <div class="fw-bold text-decoration-underline">{{ $pejabat[$spj->pejabat_pengadaan_id]->nama ?? '...................' }}</div>
            <div>NIP: {{ $pejabat[$spj->pejabat_pengadaan_id]->nip ?? '-' }}</div>
        </div>
    </div>
</div>