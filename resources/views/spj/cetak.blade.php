<div class="tab-content" id="myTabContent">
    @if($spj->jenis_pengeluaran === 'pihak_ketiga')
        <div class="tab-pane fade show active" id="nopes">@include('spj.cetak.nopes')</div>
        <div class="tab-pane fade" id="pengadaan">@include('spj.cetak.pengadaan')</div>
        <div class="tab-pane fade" id="pemeriksaan">@include('spj.cetak.pemeriksaan')</div>
        <div class="tab-pane fade" id="penyerahan">@include('spj.cetak.penyerahan')</div>
        <div class="tab-pane fade" id="penerimaan">@include('spj.cetak.penerimaan')</div>
        <div class="tab-pane fade" id="kwitansi">@include('spj.cetak.kwitansi')</div>

    @elseif($spj->jenis_pengeluaran === 'honor')
        <div class="tab-pane fade show active" id="kwitansi">@include('spj.cetak.kwitansi')</div>
        <div class="tab-pane fade" id="lampiran-honor">@include('spj.cetak.lampiran-honor')</div>

    @elseif($spj->jenis_pengeluaran === 'gaji')
        <div class="tab-pane fade show active" id="kwitansi">@include('spj.cetak.kwitansi')</div>

    @elseif($spj->jenis_pengeluaran === 'belanja_modal')
        <div class="tab-pane fade show active" id="kwitansi">@include('spj.cetak.kwitansi')</div>
        <div class="tab-pane fade" id="penerimaan">@include('spj.cetak.penerimaan')</div>
    @endif
</div>