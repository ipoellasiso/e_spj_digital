<div class="page">
    @include('spj.cetak.kop')
    <div class="judul-dokumen">DAFTAR PENERIMA HONORARIUM</div>
    <div class="text-center mb-4">Nomor : {{ $spj->nomor }}/HONOR/{{ $spj->singkatan_opd }}/{{ $spj->tanggal->format('M') }}/{{ $spj->tahun }}</div>

    <p>Sub Kegiatan : {{ $spj->anggaranRekening->subKegiatan->uraian }}<br>
       Uraian : {{ $spj->uraian }}</p>

    <table class="tabel-item">
        <thead>
            <tr><th width="5%">No</th><th>Nama Penerima</th><th>Jabatan / Uraian</th><th class="text-end">Jumlah (Rp)</th><th width="20%">Tanda Tangan</th></tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($spj->items as $i => $item)
            <tr>
                <td class="text-center">{{ $i+1 }}</td>
                <td>{{ $item->uraian }}</td>
                <td class="text-center">{{ $item->satuan }}</td>
                <td class="text-end">{{ \App\Support\Uang::rupiah($item->jumlah) }}</td>
                <td></td>
            </tr>
            @php $total += $item->jumlah; @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr><th colspan="3" class="text-end">Total</th><th class="text-end">{{ \App\Support\Uang::rupiah($total) }}</th><th></th></tr>
            <tr><td colspan="5" class="fst-italic">Terbilang: {{ ucwords(terbilang($total)) }} Rupiah</td></tr>
        </tfoot>
    </table>

    <div class="area-ttd mt-5 d-flex justify-content-end">
        <div class="kotak-ttd">
            <div>Palu, {{ $spj->tanggal->translatedFormat('d F Y') }}</div>
            <div class="fw-bold">{{ $pejabat[$spj->pptk_id]->jabatan ?? 'Pejabat Pelaksana Teknis Kegiatan' }}</div>
            <div class="ruang-ttd"></div>
            <div class="fw-bold text-decoration-underline">{{ $pejabat[$spj->pptk_id]->nama ?? '...................' }}</div>
            <div>NIP: {{ $pejabat[$spj->pptk_id]->nip ?? '-' }}</div>
        </div>
    </div>
</div>