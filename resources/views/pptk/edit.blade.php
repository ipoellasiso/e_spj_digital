@extends('layouts.app')

@section('title', 'Penugasan ' . $pptk->name)
@section('nav', 'pptk')

@section('content')
    {{-- Wrapper dibuat full width tanpa batasan max-width --}}
    <div class="w-100">
        {{-- Tombol Kembali & Judul --}}
        <div class="mb-4">
            <a href="{{ route('pptk.index', ['tahun' => $tahun]) }}" class="back-link">
                <i class="bi bi-arrow-left"></i> Penugasan PPTK
            </a>
            <h1 class="form-title mb-1">{{ $pptk->name }}</h1>
            <p class="text-muted small">Sub kegiatan yang menjadi tanggung jawabnya pada tahun anggaran <strong>{{ $tahun }}</strong>.</p>
        </div>

        @if (! $versi)
            <div class="alert alert-warning border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> DPA Belum Diimpor</h5>
                <p class="mb-0">DPA tahun {{ $tahun }} belum diimpor. Silakan impor DPA terlebih dahulu, lalu atur penugasannya.</p>
            </div>
        @else
            <form method="POST" action="{{ route('pptk.update', $pptk) }}" class="form-card p-0 overflow-hidden">
                @csrf 
                @method('PUT')
                <input type="hidden" name="tahun" value="{{ $tahun }}">

                {{-- Error Handling --}}
                @if ($errors->any())
                    <div class="alert alert-danger m-4 mb-0 border-0">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Toolbar Sticky --}}
                <div class="p-4 border-bottom bg-white position-sticky top-0" style="z-index: 10;">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div class="input-cari flex-grow-1" style="max-width: 450px;">
                            <i class="bi bi-search"></i>
                            <input id="cari" type="search" placeholder="Cari kode atau nama sub kegiatan..." aria-label="Cari sub kegiatan" style="width: 100%;">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn-batal py-2 px-3 text-sm" id="pilih-tampil">Pilih Semua</button>
                            <button type="button" class="btn-batal py-2 px-3 text-sm" id="lepas-tampil">Lepas Semua</button>
                        </div>
                        <div class="ms-auto text-secondary fw-semibold small">
                            <span id="jumlah" class="text-success fs-5">0</span> dipilih dari {{ $sub->count() }}
                        </div>
                    </div>
                </div>

                {{-- Daftar Checkbox (Tinggi dibuat lebih lega) --}}
                <div style="max-height: 65vh; overflow-y: auto;">
                    @forelse ($sub as $s)
                        @php $lain = $dipakaiLain[$s->kode] ?? null; @endphp
                        
                        <label class="item-sub d-flex gap-3 align-items-start p-3 border-bottom {{ $lain ? 'bg-light opacity-75' : '' }}" style="cursor: {{ $lain ? 'not-allowed' : 'pointer' }}; transition: all 0.2s ease;">
                            <input class="form-check-input mt-1 cek" type="checkbox" name="sub[]" value="{{ $s->kode }}"
                                   @checked(in_array($s->kode, (array) $terpilih, true)) @disabled($lain)
                                   style="width: 20px; height: 20px; cursor: inherit;">
                            
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="badge bg-secondary mb-1">{{ $s->kode }}</span>
                                        <div class="fw-semibold text-dark">{{ $s->uraian }}</div>
                                    </div>
                                    <div class="text-end ms-3">
                                        <div class="small text-muted mb-1">Pagu</div>
                                        <div class="fw-bold angka text-success">Rp{{ number_format((float) $s->pagu, 2, ',', '.') }}</div>
                                    </div>
                                </div>
                                @if ($lain)
                                    <div class="small text-danger mt-1"><i class="bi bi-lock-fill"></i> Sudah ditugaskan ke {{ $lain }}</div>
                                @endif
                            </div>
                        </label>
                    @empty
                        <div class="p-5 text-center text-muted">
                            <i class="bi bi-folder-x fs-1 mb-3 d-block opacity-50"></i>
                            <p class="mb-0">Belum ada sub kegiatan untuk DPA tahun ini.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Footer Aksi --}}
                <div class="p-4 bg-light border-top d-flex gap-3 justify-content-end">
                    <a href="{{ route('pptk.index', ['tahun' => $tahun]) }}" class="btn-batal">Batal</a>
                    <button type="submit" class="btn-premium">
                        <i class="bi bi-save me-1"></i> Simpan Penugasan
                    </button>
                </div>
            </form>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var cari = document.getElementById('cari');
            if (!cari) return;
            var item = Array.prototype.slice.call(document.querySelectorAll('.item-sub'));
            
            function hitung() { 
                document.getElementById('jumlah').textContent = document.querySelectorAll('.cek:checked').length; 
            }
            
            function tampil(el) { 
                return el.style.display !== 'none'; 
            }
            
            cari.addEventListener('input', function () {
                var q = cari.value.toLowerCase();
                item.forEach(function (el) { 
                    el.style.display = el.textContent.toLowerCase().indexOf(q) === -1 ? 'none' : ''; 
                });
            });
            
            document.getElementById('pilih-tampil').addEventListener('click', function () {
                item.filter(tampil).forEach(function (el) { 
                    var c = el.querySelector('.cek'); 
                    if (!c.disabled) c.checked = true; 
                }); 
                hitung();
            });
            
            document.getElementById('lepas-tampil').addEventListener('click', function () {
                item.filter(tampil).forEach(function (el) { 
                    var c = el.querySelector('.cek'); 
                    if (!c.disabled) c.checked = false; 
                }); 
                hitung();
            });
            
            document.addEventListener('change', function (e) { 
                if (e.target.classList.contains('cek')) hitung(); 
            });
            
            hitung();
        })();
    </script>
@endpush