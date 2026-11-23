@extends('layouts.app')

@section('title', 'SPJ ' . ($spj->nomor ?? 'draft'))
@section('nav', 'spj')

@section('content')
    @php
        $rp = fn ($v) => \App\Support\Uang::rupiah(\App\Support\Uang::sen($v));
        $ar = $spj->anggaranRekening;
        $setelah = $sisaSen - ($spj->status->bisaDiubah() ? \App\Support\Uang::sen($spj->total) : 0);
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

        {{-- Tombol Aksi Verifikasi --}}
        @php
            $user = auth()->user();
            $bisaVerif = ($user->hasRole('bendahara') && $spj->status === \App\Enums\SpjStatus::Diajukan) ||
                         ($user->hasRole('ppk_skpd') && $spj->status === \App\Enums\SpjStatus::DiverifikasiBendahara) ||
                         ($user->hasRole('pa') && $spj->status === \App\Enums\SpjStatus::DiverifikasiPpk);
        @endphp

        @if ($bisaVerif)
            <div class="d-flex flex-wrap gap-2 mt-3">
                <button type="button" class="btn-premium" data-bs-toggle="modal" data-bs-target="#modalSetuju">
                    <i class="bi bi-check-circle"></i> Setujui SPJ
                </button>
                <button type="button" class="btn-batal text-danger" data-bs-toggle="modal" data-bs-target="#modalTolak">
                    <i class="bi bi-x-circle"></i> Tolak / Kembalikan
                </button>
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
                    <div class="mb-3 border-start border-3 {{ str_contains(strtolower($a->aksi), 'tolak') ? 'border-danger' : 'border-success' }} ps-3">
                        <div class="fw-bold text-dark">{{ ucfirst($a->aksi) }}</div>
                        <div class="small text-secondary">{{ $a->user?->name ?? '-' }}, {{ $a->created_at->format('d/m/Y H:i') }}</div>
                        @if ($a->catatan)
                            <div class="small mt-1 fst-italic text-danger bg-danger bg-opacity-10 p-2 rounded">"{{ $a->catatan }}"</div>
                        @endif
                    </div>
                @empty
                    <div class="text-secondary small">Belum ada riwayat.</div>
                @endforelse
            </section>
        </div>
    </div>

        {{-- Modal Setuju --}}
    <div class="modal fade" id="modalSetuju" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('verifikasi.proses', $spj) }}" class="modal-content border-0 shadow-lg rounded-4">
                @csrf
                <input type="hidden" name="aksi" value="setuju">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-success">Setujui SPJ?</h5>
                </div>
                <div class="modal-body px-4">
                    <p class="text-muted small">Pastikan dokumen dan nominal sudah sesuai.</p>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn-batal" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-premium">Ya, Setujui</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Tolak --}}
    <div class="modal fade" id="modalTolak" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('verifikasi.proses', $spj) }}" class="modal-content border-0 shadow-lg rounded-4">
                @csrf
                <input type="hidden" name="aksi" value="tolak">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-danger">Tolak / Kembalikan SPJ</h5>
                </div>
                <div class="modal-body px-4">
                    <p class="text-muted small">Pagu yang ditahan akan dilepas dan SPJ dikembalikan ke pembuatnya.</p>
                    <label class="form-label fw-bold text-secondary small">Alasan Penolakan</label>
                    <textarea name="catatan" class="form-control" rows="3" required placeholder="Tulis alasan mengapa SPJ ini ditolak..."></textarea>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn-batal" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-premium bg-danger border-danger">Ya, Tolak SPJ</button>
                </div>
            </form>
        </div>
    </div>
@endsection