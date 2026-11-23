<div class="page">
    @include('spj.cetak.kop')
    <div class="judul-dokumen">BERITA ACARA PENYERAHAN BARANG</div>
    <div class="text-center mb-4">Nomor : {{ $spj->nomor }}/BAST/{{ $spj->singkatan_opd }}/{{ $spj->tanggal->format('M') }}/{{ $spj->tahun }}</div>

    <p>Pada hari ini <strong>{{ $spj->tanggal->translatedFormat('l') }}</strong> tanggal <strong>{{ ucwords(terbilang($spj->tanggal->day)) }}</strong> bulan <strong>{{ $spj->tanggal->translatedFormat('F') }}</strong> tahun <strong>{{ ucwords(terbilang($spj->tanggal->year)) }}</strong>, berdasarkan Berita Acara Pemeriksaan Barang :</p>
    <p>Nomor : {{ $spj->nomor }}/BAPP/{{ $spj->singkatan_opd }}/{{ $spj->tanggal->format('M') }}/{{ $spj->tahun }}<br>
    Tanggal : {{ $spj->tanggal->translatedFormat('d F Y') }}</p>
    
    <p>Dengan ini telah melakukan serah terima hasil pekerjaan sesuai dengan di bawah ini :</p>
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
    
    <p>Demikian berita acara ini dibuat untuk dipergunakan sebagaimana mestinya.</p>

    <div class="area-ttd mt-5 d-flex justify-content-between">
        <div class="kotak-ttd">
            <div>Rekanan,</div>
            <div class="ruang-ttd"></div>
            <div class="fw-bold text-decoration-underline">{{ $spj->penyedia->nama ?? '...................' }}</div>
        </div>
        <div class="kotak-ttd">
            <div>Diterima Oleh,</div>
            <div class="fw-bold">{{ $pejabat[$spj->pembuat_komitmen_id]->jabatan ?? 'Pejabat Pembuat Komitmen' }}</div>
            <div class="ruang-ttd"></div>
            <div class="fw-bold text-decoration-underline">{{ $pejabat[$spj->pembuat_komitmen_id]->nama ?? '...................' }}</div>
            <div>NIP: {{ $pejabat[$spj->pembuat_komitmen_id]->nip ?? '-' }}</div>
        </div>
    </div>
</div>