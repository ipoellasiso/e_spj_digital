@extends('layouts.app')

@section('title', 'SPJ ' . ($spj->nomor ?? 'draft'))
@section('nav', 'spj')

@section('content')
    @php
        $rp = fn ($v) => \App\Support\Uang::rupiah(\App\Support\Uang::sen($v));
        $ar = $spj->anggaranRekening;
        $setelah = $sisaSen - ($spj->status->bisaDiubah() ? \App\Support\Uang::sen($spj->total) : 0);
        $tindakan = app(\App\Services\SpjWorkflowService::class)->tindakanTersedia($spj, auth()->user());
        $aksiLabel = ['diajukan' => 'Diajukan', 'diverifikasi' => 'Diverifikasi', 'disetujui' => 'Disetujui', 'dikembalikan' => 'Dikembalikan', 'dibatalkan' => 'Dibatalkan'];
        $tahapLabel = ['pengajuan' => '', 'bendahara' => 'oleh bendahara', 'ppk_skpd' => 'oleh PPK-SKPD', 'pa' => 'oleh pengguna anggaran', 'pembatalan' => ''];
    @endphp

    <div class="mb-3">
        <a href="{{ route('spj.index') }}" class="small"><i class="bi bi-arrow-left"></i> Daftar SPJ</a>
    </div>

    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1" style="color:var(--hutan)">
                SPJ {{ $spj->nomor ?? '(belum bernomor)' }}
                <span class="st {{ $spj->status->badge() }} ms-2 align-middle">{{ $spj->status->label() }}</span>
            </h1>
            <p class="text-secondary mb-0" style="max-width:70ch">{{ $spj->uraian }}</p>
        </div>

        @if ($bolehKelola)
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('spj.edit', $spj) }}" class="btn btn-outline-daun"><i class="bi bi-pencil me-1"></i> Ubah</a>
                <form method="POST" action="{{ route('spj.ajukan', $spj) }}" onsubmit="return confirm('Ajukan SPJ ini? Pagu rekening akan ditahan dan SPJ tidak bisa diubah lagi.')">
                    @csrf
                    <button type="submit" class="btn btn-kuning"><i class="bi bi-send me-1"></i> Ajukan</button>
                </form>
                @if ($spj->status === \App\Enums\SpjStatus::Draft && $spj->nomor_urut === null)
                    <form method="POST" action="{{ route('spj.destroy', $spj) }}" onsubmit="return confirm('Hapus draft SPJ ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-light text-danger">Hapus</button>
                    </form>
                @endif
            </div>
        @endif
    </div>

    @error('ajukan')
        <div class="alert alert-danger" role="alert">
            <strong>SPJ belum bisa diajukan.</strong>
            <div style="white-space:pre-line" class="mt-1">{{ $message }}</div>
        </div>
    @enderror
    @error('spj')<div class="alert alert-danger" role="alert" style="white-space:pre-line">{{ $message }}</div>@enderror

    @error('proses')<div class="alert alert-danger" role="alert">{{ $message }}</div>@enderror

    @if ($tindakan['maju'] || $tindakan['kembalikan'] || $tindakan['batalkan'] || $tindakan['tertahan'])
        <section class="panel p-4 mb-4" style="border-left:4px solid var(--kuning)">
            <h2 class="h6 fw-bold mb-2" style="color:var(--hutan)">
                {{ $spj->status === \App\Enums\SpjStatus::Final ? 'Pembatalan SPJ final' : 'Menunggu tindakan Anda' }}
            </h2>
            @if ($tindakan['tertahan'])
                <div class="alert alert-warning py-2 small" role="status">{{ $tindakan['tertahan'] }}</div>
            @endif
            <form method="POST" action="{{ route('spj.proses', $spj) }}">
                @csrf
                <label for="catatan" class="form-label fw-semibold small">
                    Catatan <span class="text-secondary fw-normal">(wajib bila mengembalikan atau membatalkan)</span>
                </label>
                <textarea id="catatan" name="catatan" rows="2" maxlength="1000" class="form-control mb-3"
                          placeholder="Tulis apa yang perlu diperbaiki, atau alasan pembatalan"></textarea>
                <div class="d-flex flex-wrap gap-2">
                    @if ($tindakan['maju'])
                        <button type="submit" name="aksi" value="maju" class="btn btn-kuning"
                                onclick="return confirm('{{ $tindakan['maju'] }} SPJ ini?')">
                            <i class="bi bi-check2-circle me-1"></i> {{ $tindakan['maju'] }}
                        </button>
                    @endif
                    @if ($tindakan['kembalikan'])
                        <button type="submit" name="aksi" value="kembalikan" class="btn btn-outline-danger"
                                onclick="return confirm('Kembalikan SPJ ini ke pembuatnya? Pagu yang ditahan akan dilepas.')">
                            <i class="bi bi-arrow-return-left me-1"></i> Kembalikan
                        </button>
                    @endif
                    @if ($tindakan['batalkan'])
                        <button type="submit" name="aksi" value="batalkan" class="btn btn-light text-danger"
                                onclick="return confirm('Batalkan SPJ ini? Tindakan ini tidak bisa diurungkan.')">
                            <i class="bi bi-x-octagon me-1"></i> Batalkan SPJ
                        </button>
                    @endif
                </div>
            </form>
        </section>
    @endif

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <section class="panel p-4 mb-4">
                <div class="row g-3">
                    <div class="col-6 col-md-4"><div class="text-secondary small">Tanggal</div><div class="fw-semibold angka">{{ $spj->tanggal->format('d/m/Y') }}</div></div>
                    <div class="col-6 col-md-4"><div class="text-secondary small">Jenis</div><div class="fw-semibold">{{ \App\Models\Spj::JENIS[$spj->jenis] ?? $spj->jenis }}</div></div>
                    <div class="col-6 col-md-4"><div class="text-secondary small">Sumber dana</div><div class="fw-semibold">{{ $spj->sumberDana?->kode }}</div></div>
                    <div class="col-12"><div class="text-secondary small">Sub kegiatan</div><div class="fw-semibold"><span class="angka">{{ $ar->subKegiatan->kode }}</span> {{ $ar->subKegiatan->uraian }}</div></div>
                    <div class="col-12"><div class="text-secondary small">Rekening belanja</div><div class="fw-semibold"><span class="angka">{{ $ar->rekening->kode }}</span> {{ $ar->rekening->nama }}</div></div>
                    <div class="col-12 col-md-6"><div class="text-secondary small">Penyedia</div><div class="fw-semibold">{{ $spj->penyedia?->nama ?? '-' }}</div></div>
                    <div class="col-12 col-md-6"><div class="text-secondary small">Dibuat oleh</div><div class="fw-semibold">{{ $spj->pembuat?->name ?? '-' }}</div></div>
                </div>
            </section>

            <section class="panel mb-4">
                <div class="panel-kepala"><h2>Item belanja</h2></div>
                <div class="table-responsive">
                    <table class="table tabel">
                        <thead><tr><th>No</th><th>Uraian</th><th class="text-end">Volume</th><th>Satuan</th><th class="text-end">Harga satuan</th><th class="text-end">Jumlah</th></tr></thead>
                        <tbody class="angka">
                        @foreach ($spj->items as $i => $it)
                            <tr>
                                <td>{{ $i + 1 }}</td><td>{{ $it->uraian }}</td>
                                <td class="text-end">{{ \App\Support\Uang::isianVolume($it->volume) }}</td><td>{{ $it->satuan }}</td>
                                <td class="text-end">{{ $rp($it->harga_satuan) }}</td><td class="text-end">{{ $rp($it->jumlah) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot><tr><th colspan="5" class="text-end">Total</th><th class="text-end angka">{{ $rp($spj->total) }}</th></tr></tfoot>
                    </table>
                </div>
            </section>

            <section class="panel p-4">
                <h2 class="h6 fw-bold mb-3" style="color:var(--hutan)">Pejabat penandatangan</h2>
                <div class="row g-3">
                    @foreach (\App\Models\Spj::PEJABAT as $kolom => [$label])
                        <div class="col-md-6">
                            <div class="text-secondary small">{{ $label }}</div>
                            <div class="fw-semibold">{{ $pejabat[$spj->{$kolom}]->nama ?? '-' }}</div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="col-12 col-xl-4">
            <section class="panel p-4 mb-4">
                <h2 class="h6 fw-bold mb-3" style="color:var(--hutan)">Posisi pagu rekening</h2>
                <dl class="row mb-0 angka small">
                    <dt class="col-7 fw-normal text-secondary">Pagu</dt><dd class="col-5 text-end">{{ $rp($ar->pagu) }}</dd>
                    <dt class="col-7 fw-normal text-secondary">SPJ final</dt><dd class="col-5 text-end">{{ $rp($ar->realisasi_final) }}</dd>
                    <dt class="col-7 fw-normal text-secondary">Dalam proses</dt><dd class="col-5 text-end">{{ $rp($ar->reserved) }}</dd>
                    <dt class="col-7 fw-semibold">Sisa pagu</dt><dd class="col-5 text-end fw-semibold">{{ \App\Support\Uang::rupiah($sisaSen) }}</dd>
                    @if ($spj->status->bisaDiubah())
                        <dt class="col-7 fw-normal text-secondary">SPJ ini</dt><dd class="col-5 text-end">{{ $rp($spj->total) }}</dd>
                        <dt class="col-7 fw-semibold">Sisa setelah diajukan</dt>
                        <dd class="col-5 text-end fw-semibold {{ $setelah < 0 ? 'text-danger' : '' }}">{{ \App\Support\Uang::rupiah($setelah) }}</dd>
                    @endif
                </dl>
                @if ($spj->status->bisaDiubah() && $setelah < 0)
                    <div class="alert alert-danger py-2 mt-3 mb-0 small">Melebihi sisa pagu. SPJ ini tidak bisa diajukan.</div>
                @endif
            </section>

            <section class="panel p-4">
                <h2 class="h6 fw-bold mb-3" style="color:var(--hutan)">Riwayat</h2>
                @forelse ($spj->approval as $a)
                    <div class="mb-2">
                        <div class="fw-semibold">{{ trim(($aksiLabel[$a->aksi] ?? ucfirst($a->aksi)) . ' ' . ($tahapLabel[$a->tahap] ?? '')) }}</div>
                        <div class="small text-secondary">{{ $a->user?->name ?? '-' }}, {{ $a->created_at->format('d/m/Y H:i') }}</div>
                        @if ($a->catatan)<div class="small">{{ $a->catatan }}</div>@endif
                    </div>
                @empty
                    <div class="text-secondary small">Belum ada riwayat. SPJ masih draft.</div>
                @endforelse
            </section>
        </div>
    </div>
@endsection