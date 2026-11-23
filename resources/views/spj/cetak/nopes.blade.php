<div class="page">
    @include('spj.cetak.kop')
    <div class="judul-dokumen">NOTA PESANAN</div>
    
    <table class="tabel-data">
        <tr>
            <td width="15%">Nomor</td><td width="35%">: {{ $spj->nomor ?? '...' }}/NP/{{ $spj->singkatan_opd ?? 'TWL' }}/{{ $spj->tanggal->format('M') }}/{{ $spj->tahun }}</td>
            <td width="15%">Tanggal</td><td width="35%">: {{ $spj->tanggal->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td>Dari</td><td colspan="3">: {{ $pejabat[$spj->pembuat_komitmen_id]->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td>Jabatan</td><td colspan="3">: {{ $pejabat[$spj->pembuat_komitmen_id]->jabatan ?? 'Pejabat Pembuat Komitmen' }}</td>
        </tr>
        <tr>
            <td>Dasar</td><td colspan="3">: DPA/{{ $spj->anggaranRekening->subKegiatan->kode ?? '-' }}/{{ $spj->tahun }}</td>
        </tr>
        <tr>
            <td>Kepada</td><td colspan="3">: <strong>{{ $spj->penyedia->nama ?? '-' }}</strong></td>
        </tr>
    </table>

    <p><strong>Isi Pesanan:</strong> {{ $spj->uraian }}</p>
    
    <table class="tabel-item">
        <thead>
            <tr><th width="5%">No</th><th>Nama Barang / Jasa</th><th width="10%">Volume</th><th width="15%">Satuan</th><th width="20%">Harga</th><th width="20%">Jumlah</th></tr>
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
            <tr>
                <th colspan="5" class="text-end">Total</th>
                <th class="text-end">{{ \App\Support\Uang::rupiah($spj->total) }}</th>
            </tr>
            <tr>
                <td colspan="6" class="p-2 fst-italic">Terbilang: {{ ucwords(terbilang($spj->total)) }} Rupiah</td>
            </tr>
        </tfoot>
    </table>
    
    <div class="area-ttd d-flex justify-content-between mt-5">
        <div class="kotak-ttd">
            <div>Rekanan,</div>
            <div class="ruang-ttd"></div>
            <div class="fw-bold text-decoration-underline">{{ $spj->penyedia->nama ?? '...................' }}</div>
        </div>
        <div class="kotak-ttd">
            <div>Palu, {{ $spj->tanggal->translatedFormat('d F Y') }}</div>
            <div class="fw-bold">{{ $pejabat[$spj->pembuat_komitmen_id]->jabatan ?? 'Pejabat Pembuat Komitmen' }}</div>
            <div class="ruang-ttd"></div>
            <div class="fw-bold text-decoration-underline">{{ $pejabat[$spj->pembuat_komitmen_id]->nama ?? '...................' }}</div>
            <div>NIP: {{ $pejabat[$spj->pembuat_komitmen_id]->nip ?? '-' }}</div>
        </div>
    </div>
</div>