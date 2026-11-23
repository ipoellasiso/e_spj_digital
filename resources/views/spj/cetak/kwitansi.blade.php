<div class="page">
    @include('spj.cetak.kop')
    
    <table class="tabel-data" style="margin-bottom: 20px;">
        <tr>
            <td>Tahun</td><td>: {{ $spj->tahun }}</td>
            <td>Sumber Dana</td><td>: {{ $spj->sumberDana->kode }}</td>
        </tr>
        <tr>
            <td>Program</td><td colspan="3">: {{ $spj->anggaranRekening->subKegiatan->kegiatan->program->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td>Kegiatan</td><td colspan="3">: {{ $spj->anggaranRekening->subKegiatan->kegiatan->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td>Sub Kegiatan</td><td colspan="3">: {{ $spj->anggaranRekening->subKegiatan->uraian ?? '-' }}</td>
        </tr>
        <tr>
            <td>Rekening Belanja</td><td colspan="3">: {{ $spj->anggaranRekening->rekening->kode }} - {{ $spj->anggaranRekening->rekening->nama }}</td>
        </tr>
    </table>

    <div class="judul-dokumen">TANDA PENERIMAAN</div>
    
    <table class="tabel-data" style="border: 2px solid #000; padding: 10px;">
        <tr><td width="25%">Sudah Terima Dari</td><td width="75%">: <strong>{{ $pejabat[$spj->pembuat_komitmen_id]->nama ?? '-' }}</strong></td></tr>
        <tr><td>Terbilang</td><td>: <strong><em>{{ ucwords(terbilang($spj->total)) }} Rupiah</em></strong></td></tr>
        <tr><td>Untuk Pembayaran</td><td>: {{ $spj->uraian }}</td></tr>
        <tr><td>Uang Sebesar</td><td>: <strong style="font-size: 18px;">{{ \App\Support\Uang::rupiah($spj->total) }}</strong></td></tr>
    </table>

    <div class="area-ttd mt-5 d-flex justify-content-between">
        <div class="kotak-ttd">
            <div>Menyetujui,</div>
            <div class="fw-bold">Pejabat Pelaksana Teknis Kegiatan</div>
            <div class="ruang-ttd"></div>
            <div class="fw-bold text-decoration-underline">{{ $pejabat[$spj->pptk_id]->nama ?? '...................' }}</div>
            <div>NIP: {{ $pejabat[$spj->pptk_id]->nip ?? '-' }}</div>
        </div>
        <div class="kotak-ttd">
            <div>Yang Membayarkan,</div>
            <div class="fw-bold">Bendahara Pengeluaran</div>
            <div class="ruang-ttd"></div>
            <div class="fw-bold text-decoration-underline">{{ $pejabat[$spj->bendahara_id]->nama ?? '...................' }}</div>
            <div>NIP: {{ $pejabat[$spj->bendahara_id]->nip ?? '-' }}</div>
        </div>
        <div class="kotak-ttd">
            <div>Palu, {{ $spj->tanggal->translatedFormat('d F Y') }}</div>
            <div class="fw-bold">Yang Menerima</div>
            <div class="ruang-ttd"></div>
            <div class="fw-bold text-decoration-underline">{{ $spj->penyedia->nama ?? $pejabat[$spj->bendahara_id]->nama ?? '...................' }}</div>
        </div>
    </div>

    <div class="text-center mt-4">
        <div>Mengetahui,</div>
        <div class="fw-bold">Pengguna Anggaran</div>
        <div class="ruang-ttd"></div>
        <div class="fw-bold text-decoration-underline">{{ $pejabat[$spj->pengguna_anggaran_id]->nama ?? '...................' }}</div>
        <div>NIP: {{ $pejabat[$spj->pengguna_anggaran_id]->nip ?? '-' }}</div>
    </div>
</div>