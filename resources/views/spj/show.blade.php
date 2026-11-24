@extends('layouts.app')

@section('title', 'SPJ ' . ($spj->nomor ?? 'draft'))
@section('nav', 'spj')

@push('styles')
<style>
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.06) !important;
    }
    .status-badge {
        font-size: 0.85rem;
        padding: 0.5em 1em;
        border-radius: 999px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
</style>
@endpush

@section('content')
    @php
        $rp = fn ($v) => \App\Support\Uang::rupiah(\App\Support\Uang::sen($v));
        $ar = $spj->anggaranRekening;
        $setelah = $sisaSen - ($spj->status->bisaDiubah() ? \App\Support\Uang::sen($spj->total) : 0);
        $tindakan = app(\App\Services\SpjWorkflowService::class)->tindakanTersedia($spj, auth()->user());
        $aksiLabel = ['diajukan' => 'Diajukan', 'diverifikasi' => 'Diverifikasi', 'disetujui' => 'Disetujui', 'dikembalikan' => 'Dikembalikan', 'dibatalkan' => 'Dibatalkan'];
        $tahapLabel = ['pengajuan' => '', 'bendahara' => 'oleh bendahara', 'ppk_skpd' => 'oleh PPK-SKPD', 'pa' => 'oleh pengguna anggaran', 'pembatalan' => ''];
    @endphp

    {{-- Header Halaman --}}
    <div class="page-header align-items-start">
        <div>
            <a href="{{ route('spj.index') }}" class="back-link mb-2">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar SPJ
            </a>
            <div class="d-flex align-items-center gap-3 mb-2">
                <h1 class="page-title mb-0">SPJ {{ $spj->nomor ?? '(Draft)' }}</h1>
                <span class="badge {{ $spj->status->badge() }} status-badge">{{ $spj->status->label() }}</span>
            </div>
            <p class="page-desc mb-0">{{ $spj->uraian }}</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            {{-- Tombol Cetak Selalu Muncul --}}
            <a href="{{ route('spj.cetak', $spj) }}" target="_blank" class="btn-premium" style="background: white; color: var(--hijau-800); border: 1px solid var(--border-color); box-shadow: none;">
                <i class="bi bi-printer"></i> Cetak Dokumen
            </a>

            {{-- Tombol Aksi (Jika Draft) --}}
            @if ($bolehKelola)
                <a href="{{ route('spj.edit', $spj) }}" class="btn-premium">
                    <i class="bi bi-pencil"></i> Ubah
                </a>
                <form method="POST" action="{{ route('spj.ajukan', $spj) }}" onsubmit="return confirm('Ajukan SPJ ini? Pagu rekening akan ditahan dan SPJ tidak bisa diubah lagi.')">
                    @csrf
                    <button type="submit" class="btn-premium" style="background: linear-gradient(135deg, #d97706, #fbbf24);">
                        <i class="bi bi-send"></i> Ajukan
                    </button>
                </form>
                @if ($spj->status === \App\Enums\SpjStatus::Draft && $spj->nomor_urut === null)
                    <form method="POST" action="{{ route('spj.destroy', $spj) }}" onsubmit="return confirm('Hapus draft SPJ ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-batal text-danger">
                            <i class="bi bi-trash3"></i> Hapus
                        </button>
                    </form>
                @endif
            @endif
        </div>
    </div>

    {{-- Pesan Error --}}
    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            <ul class="mb-0">
                @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4 mt-2">
        {{-- KOLOM KIRI (DETAIL & ITEM) --}}
        <div class="col-12 col-xl-8">
            
            {{-- Panel Aksi Verifikasi (Jika Ada) --}}
            @if ($tindakan['maju'] || $tindakan['kembalikan'] || $tindakan['batalkan'] || $tindakan['tertahan'])
                <section class="form-card mb-4 p-4 hover-lift" style="border-left: 5px solid var(--kuning-500); background: linear-gradient(to right, #fffbeb, #ffffff);">
                    <h2 class="h6 fw-bold mb-3 text-dark">
                        <i class="bi bi-shield-exclamation text-warning me-2"></i>
                        {{ $spj->status === \App\Enums\SpjStatus::Final ? 'Pembatalan SPJ Final' : 'Menunggu Tindakan Anda' }}
                    </h2>
                    
                    @if ($tindakan['tertahan'])
                        <div class="alert alert-warning py-2 small border-0 mb-3">{{ $tindakan['tertahan'] }}</div>
                    @endif

                    <form method="POST" action="{{ route('spj.proses', $spj) }}" id="form-proses-spj">
                        @csrf
                        <div class="mb-3">
                            <label for="catatan" class="form-label fw-bold text-secondary small">
                                Catatan <span class="fw-normal">(Wajib bila mengembalikan atau membatalkan)</span>
                            </label>
                            <textarea id="catatan" name="catatan" rows="2" maxlength="1000" class="form-control" placeholder="Tulis alasan penolakan atau perbaikan..."></textarea>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @if ($tindakan['maju'])
                                <button type="button" class="btn-premium bg-success border-success btn-konfirmasi" 
                                        data-aksi="maju" 
                                        data-pesan="{{ $tindakan['maju'] }} SPJ ini?">
                                    <i class="bi bi-check2-circle"></i> {{ $tindakan['maju'] }}
                                </button>
                            @endif
                            @if ($tindakan['kembalikan'])
                                <button type="button" class="btn-batal text-danger btn-konfirmasi" 
                                        data-aksi="kembalikan" 
                                        data-pesan="Kembalikan SPJ ini ke pembuatnya? Pagu yang ditahan akan dilepas.">
                                    <i class="bi bi-arrow-return-left"></i> Kembalikan
                                </button>
                            @endif
                            @if ($tindakan['batalkan'])
                                <button type="button" class="btn-batal text-danger btn-konfirmasi" 
                                        data-aksi="batalkan" 
                                        data-pesan="Batalkan SPJ final ini? Tindakan ini tidak bisa diurungkan.">
                                    <i class="bi bi-x-octagon"></i> Batalkan SPJ
                                </button>
                            @endif
                        </div>
                    </form>
                </section>
            @endif

            {{-- Detail SPJ --}}
            <section class="form-card mb-4 hover-lift">
                <h2 class="h6 fw-bold mb-4 text-success border-bottom pb-2 d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill"></i> Rincian SPJ
                </h2>
                <div class="row g-3">
                    <div class="col-sm-6 col-md-4">
                        <div class="text-secondary small fw-bold text-uppercase mb-1">Tanggal SPJ</div>
                        <div class="fw-semibold text-dark">{{ $spj->tanggal->translatedFormat('d F Y') }}</div>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <div class="text-secondary small fw-bold text-uppercase mb-1">Jenis SPJ</div>
                        <div class="fw-semibold text-dark">{{ \App\Models\Spj::JENIS[$spj->jenis] ?? $spj->jenis }}</div>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <div class="text-secondary small fw-bold text-uppercase mb-1">Sumber Dana</div>
                        <div class="fw-semibold text-dark">{{ $spj->sumberDana?->kode ?? '-' }}</div>
                    </div>
                    
                    <div class="col-12 mt-3"><hr class="border-light my-2"></div>
                    
                    <div class="col-12">
                        <div class="text-secondary small fw-bold text-uppercase mb-1">Sub Kegiatan</div>
                        <div class="fw-semibold text-dark"><span class="badge bg-secondary me-1">{{ $ar->subKegiatan->kode }}</span> {{ $ar->subKegiatan->uraian }}</div>
                    </div>
                    <div class="col-12">
                        <div class="text-secondary small fw-bold text-uppercase mb-1">Rekening Belanja</div>
                        <div class="fw-semibold text-primary"><span class="badge bg-primary bg-opacity-10 text-primary me-1">{{ $ar->rekening->kode }}</span> {{ $ar->rekening->nama }}</div>
                    </div>
                    
                    <div class="col-12 mt-3"><hr class="border-light my-2"></div>

                    <div class="col-sm-6">
                        <div class="text-secondary small fw-bold text-uppercase mb-1">Penyedia</div>
                        <div class="fw-semibold text-dark">{{ $spj->penyedia?->nama ?? '-' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-secondary small fw-bold text-uppercase mb-1">Dibuat Oleh</div>
                        <div class="fw-semibold text-dark">{{ $spj->pembuat?->name ?? '-' }}</div>
                    </div>
                </div>
            </section>

            {{-- Item Belanja --}}
            <section class="form-card mb-4 hover-lift p-0 overflow-hidden">
                <div class="p-4 border-bottom bg-light d-flex justify-content-between align-items-center">
                    <h2 class="h6 fw-bold mb-0 text-success d-flex align-items-center gap-2">
                        <i class="bi bi-cart-fill"></i> Rincian Item Belanja
                    </h2>
                </div>
                <div class="table-responsive">
                    <table class="tabel tabel-modern w-100">
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Uraian Barang / Jasa</th>
                                <th class="text-center">Vol</th>
                                <th class="text-center">Satuan</th>
                                <th class="text-end">Harga Satuan</th>
                                <th class="text-end">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($spj->items as $i => $it)
                            <tr>
                                <td class="text-center">{{ $i + 1 }}</td>
                                <td class="fw-semibold text-dark">{{ $it->uraian }}</td>
                                <td class="text-center">{{ \App\Support\Uang::isianVolume($it->volume) }}</td>
                                <td class="text-center">{{ $it->satuan }}</td>
                                <td class="text-end">{{ $rp($it->harga_satuan) }}</td>
                                <td class="text-end fw-bold text-primary">{{ $rp($it->jumlah) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-end py-3 fs-6">TOTAL</th>
                                <th class="text-end angka fs-5 text-primary py-3">{{ $rp($spj->total) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>

            {{-- Pejabat Penandatangan --}}
            <section class="form-card hover-lift">
                <h2 class="h6 fw-bold mb-4 text-success border-bottom pb-2 d-flex align-items-center gap-2">
                    <i class="bi bi-people-fill"></i> Pejabat Penandatangan
                </h2>
                <div class="row g-4">
                    @foreach (\App\Models\Spj::PEJABAT as $kolom => [$label])
                        <div class="col-sm-6 col-md-4">
                            <div class="p-3 bg-light rounded-3 border border-light h-100">
                                <div class="text-secondary small fw-bold text-uppercase mb-1">{{ $label }}</div>
                                <div class="fw-bold text-dark">{{ $pejabat[$spj->{$kolom}]->nama ?? '-' }}</div>
                                <div class="small text-muted mt-1">{{ $pejabat[$spj->{$kolom}]->nip ?? '' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        {{-- KOLOM KANAN (PAGU & RIWAYAT) --}}
        <div class="col-12 col-xl-4">
            
            {{-- Posisi Pagu --}}
            <div class="form-card mb-4 hover-lift" style="position: sticky; top: 84px; z-index: 10;">
                <h2 class="h6 fw-bold mb-4 text-success border-bottom pb-2 d-flex align-items-center gap-2">
                    <i class="bi bi-wallet2"></i> Posisi Pagu Rekening
                </h2>
                
                <div class="p-3 bg-light rounded-3 border border-light">
                    <dl class="row mb-0 angka small">
                        <dt class="col-7 fw-normal text-secondary">Pagu Rekening</dt>
                        <dd class="col-5 text-end fw-semibold">{{ $rp($ar->pagu) }}</dd>
                        
                        <dt class="col-7 fw-normal text-secondary">Realisasi Final</dt>
                        <dd class="col-5 text-end text-success">{{ $rp($ar->realisasi_final) }}</dd>
                        
                        <dt class="col-7 fw-normal text-secondary">SPJ Dalam Proses</dt>
                        <dd class="col-5 text-end text-warning">{{ $rp($ar->reserved) }}</dd>
                        
                        <div class="col-12"><hr class="my-2 border-secondary opacity-25"></div>
                        
                        <dt class="col-7 fw-bold text-dark mt-1">Sisa Pagu</dt>
                        <dd class="col-5 text-end fw-bold text-primary mt-1">{{ \App\Support\Uang::rupiah($sisaSen) }}</dd>
                        
                        @if ($spj->status->bisaDiubah())
                            <dt class="col-7 fw-normal text-secondary mt-2">Nilai SPJ Ini</dt>
                            <dd class="col-5 text-end mt-2">{{ $rp($spj->total) }}</dd>
                            
                            <dt class="col-7 fw-bold text-dark mt-2">Sisa Setelah SPJ</dt>
                            <dd class="col-5 text-end fw-bold mt-2 {{ $setelah < 0 ? 'text-danger' : 'text-success' }}">{{ \App\Support\Uang::rupiah($setelah) }}</dd>
                        @endif
                    </dl>
                </div>
                
                @if ($spj->status->bisaDiubah() && $setelah < 0)
                    <div class="alert alert-danger py-2 mt-3 mb-0 small border-0"><i class="bi bi-exclamation-triangle-fill me-1"></i> Melebihi sisa pagu. SPJ tidak bisa diajukan.</div>
                @endif
            </div>

            {{-- Riwayat (Timeline) --}}
            <div class="form-card hover-lift">
                <h2 class="h6 fw-bold mb-4 text-success border-bottom pb-2 d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history"></i> Riwayat Proses
                </h2>
                
                <div class="timeline">
                    @forelse ($spj->approval as $a)
                        @php
                            $isTolak = str_contains(strtolower($a->aksi), 'tolak') || str_contains(strtolower($a->aksi), 'kembali') || str_contains(strtolower($a->aksi), 'batal');
                            $warna = $isTolak ? 'danger' : 'success';
                        @endphp
                        <div class="timeline-item mb-3 position-relative ps-4">
                            {{-- Garis Vertikal --}}
                            @if(!$loop->last)
                                <div class="position-absolute top-0 start-0 h-100 border-start border-2 border-light" style="left: 10px !important; z-index: 0;"></div>
                            @endif
                            
                            {{-- Titik --}}
                            <div class="position-absolute top-0 start-0 bg-{{ $warna }} rounded-circle border border-white shadow-sm" style="width: 20px; height: 20px; left: 1px !important; z-index: 1; margin-top: 2px;"></div>
                            
                            <div class="fw-bold text-{{ $warna }} text-dark">{{ trim(($aksiLabel[$a->aksi] ?? ucfirst($a->aksi)) . ' ' . ($tahapLabel[$a->tahap] ?? '')) }}</div>
                            <div class="small text-secondary mb-1">{{ $a->user?->name ?? '-' }} &middot; {{ $a->created_at->format('d M Y, H:i') }}</div>
                            
                            @if ($a->catatan)
                                <div class="small mt-2 fst-italic text-dark bg-{{ $warna }} bg-opacity-10 p-2 rounded border border-{{ $warna }}-subtle">"{{ $a->catatan }}"</div>
                            @endif
                        </div>
                    @empty
                        <div class="text-secondary small text-center py-4">
                            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                            Belum ada riwayat. SPJ masih draft.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.querySelectorAll('.btn-konfirmasi').forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                var aksi = this.getAttribute('data-aksi');
                var pesan = this.getAttribute('data-pesan');
                var form = document.getElementById('form-proses-spj');

                Swal.fire({
                    title: 'Konfirmasi Tindakan',
                    text: pesan,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: aksi === 'maju' ? '#059669' : '#ef4444', // Hijau untuk Setuju, Merah untuk Tolak
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Batal',
                    customClass: {
                        popup: 'rounded-4',
                        confirmButton: 'btn-premium',
                        cancelButton: 'btn-batal'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Buat input hidden untuk mengirim nilai aksi (maju / kembalikan / batalkan)
                        var inputAksi = document.createElement('input');
                        inputAksi.type = 'hidden';
                        inputAksi.name = 'aksi';
                        inputAksi.value = aksi;
                        
                        form.appendChild(inputAksi);
                        form.submit();
                    }
                });
            });
        });
    </script>
    @endpush
@endsection