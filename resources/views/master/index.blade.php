@extends('layouts.app')

@section('title', $judul)
@section('nav', $nav)

@push('styles')
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    {{-- CDN SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $judul }}</h1>
            @isset($deskripsi)
                <p class="page-desc">{{ $deskripsi }}</p>
            @endisset
        </div>
        
        {{-- TOMBOL TAMBAH (Dengan Perlindungan isset & pengecualian SPJ/PPTK) --}}
        @if(isset($urlBaru) && $nav != 'pptk')
            <div>
                @if($nav == 'spj')
                    {{-- Khusus SPJ: Tombol langsung ke link (tanpa modal) --}}
                    <a href="{{ $urlBaru }}" class="btn-premium">
                        <i class="bi bi-plus-lg"></i> {{ $labelBaru ?? 'Tambah' }}
                    </a>
                @else
                    {{-- Halaman lain: Tombol buka Modal --}}
                    <a href="#" class="btn-premium" data-bs-toggle="modal" data-bs-target="#modalTambahData">
                        <i class="bi bi-plus-lg"></i> {{ $labelBaru ?? 'Tambah' }}
                    </a>
                @endif
            </div>
        @endif
    </div>

    <div class="card-tabel">
        <div class="p-0">
            <table id="tabel" class="tabel tabel-modern w-100" style="width:100%">
                <thead>
                <tr>
                    @foreach ($kolom as $k)
                        <th class="{{ str_contains($k['kelas'] ?? '', 'text-end') ? 'text-end' : '' }}">{{ $k['judul'] }}</th>
                    @endforeach
                    <th class="text-end">Aksi</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- ========================================= --}}
    {{-- MODAL TAMBAH (DINAMIS) --}}
    {{-- Hanya muncul jika bukan PPTK, bukan SPJ, dan $urlBaru ada --}}
    {{-- ========================================= --}}
    @if($nav != 'pptk' && $nav != 'spj' && isset($urlBaru))
    <div class="modal fade" id="modalTambahData" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                
                <div class="modal-header border-0 pt-4 px-4 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="modalTambahLabel" style="color: var(--hijau-900); font-size: 20px;">
                            Tambah {{ $judul }}
                        </h5>
                        <p class="text-muted small mb-0 mt-1">Lengkapi data di bawah ini.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 pb-4 pt-3">
                    <form id="formTambahData" method="POST" action="{{ str_replace('/create', '', $urlBaru) }}">
                        @csrf
                        @if(isset($daftarOpd))
                            @include('partials.pilih-opd')
                        @endif

                        {{-- FORM DINAMIS BERDASARKAN MENU --}}
                        @if($nav == 'pejabat')
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Jabatan</label>
                                    <select name="jabatan" class="form-select" required>
                                        <option value="">Pilih jabatan</option>
                                        @foreach (\App\Models\PejabatOpd::JABATAN as $j)
                                            <option value="{{ $j }}">{{ $j }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Nama lengkap dengan gelar</label>
                                    <input type="text" name="nama" class="form-control" placeholder="Contoh: Budi Santoso, S.Kom" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">NIP</label>
                                    <input type="text" name="nip" class="form-control" placeholder="18 digit, boleh dengan spasi">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Mulai menjabat</label>
                                    <input type="date" name="aktif_dari" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Selesai menjabat</label>
                                    <input type="date" name="aktif_sampai" class="form-control">
                                </div>
                            </div>
                        @elseif($nav == 'sk')
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">Jenis SK</label>
                                    <select name="jenis" class="form-select" required>
                                        <option value="">Pilih Jenis</option>
                                        @foreach (\App\Http\Controllers\SkPenunjukanController::JENIS as $j)
                                            <option value="{{ $j }}">{{ $j }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Tahun</label>
                                    <input type="number" name="tahun" class="form-control" value="{{ now()->year }}" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Nomor SK</label>
                                    <input type="text" name="nomor" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Tanggal SK</label>
                                    <input type="date" name="tanggal" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Penerbit</label>
                                    <input type="text" name="penerbit" class="form-control" required>
                                </div>
                            </div>
                        @elseif($nav == 'penyedia')
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Nama Penyedia</label>
                                    <input type="text" name="nama" class="form-control" placeholder="Contoh: CV. Maju Jaya" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Alamat</label>
                                    <input type="text" name="alamat" class="form-control" placeholder="Alamat lengkap">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">NPWP</label>
                                    <input type="text" name="npwp" class="form-control" placeholder="15 atau 16 digit angka">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Rekening Bank</label>
                                    <input type="text" name="rekening_bank" class="form-control" placeholder="Contoh: BCA 12345678 a.n. CV">
                                </div>
                            </div>
                        @endif
                    </form>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-0 d-flex gap-2 justify-content-end" style="background: #f8fafc;">
                    <button type="button" class="btn-batal" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="formTambahData" class="btn-premium">
                        <i class="bi bi-save me-1"></i> Simpan Data
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endif

    {{-- ========================================= --}}
    {{-- MODAL EDIT PEJABAT --}}
    {{-- ========================================= --}}
    <div class="modal fade" id="modalEditPejabat" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header border-0 pt-4 px-4 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="modalEditLabel" style="color: var(--hijau-900); font-size: 20px;">
                            Ubah Data Pejabat
                        </h5>
                        <p class="text-muted small mb-0 mt-1">Perbarui data pejabat penandatangan di bawah ini.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 pb-4 pt-3">
                    <form id="formEditPejabat" method="POST" action="">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Jabatan</label>
                                <select name="jabatan" id="edit_jabatan" class="form-select" required>
                                    @foreach (\App\Models\PejabatOpd::JABATAN as $j)
                                        <option value="{{ $j }}">{{ $j }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Nama lengkap dengan gelar</label>
                                <input type="text" name="nama" id="edit_nama" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">NIP</label>
                                <input type="text" name="nip" id="edit_nip" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Mulai menjabat</label>
                                <input type="date" name="aktif_dari" id="edit_aktif_dari" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Selesai menjabat</label>
                                <input type="date" name="aktif_sampai" id="edit_aktif_sampai" class="form-control">
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-0 d-flex gap-2 justify-content-end" style="background: #f8fafc;">
                    <button type="button" class="btn-batal" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="formEditPejabat" class="btn-premium">
                        <i class="bi bi-save me-1"></i> Perbarui Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================= --}}
    {{-- MODAL EDIT SK PENUNJUKAN --}}
    {{-- ========================================= --}}
    <div class="modal fade" id="modalEditSk" tabindex="-1" aria-labelledby="modalEditSkLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header border-0 pt-4 px-4 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="modalEditSkLabel" style="color: var(--hijau-900); font-size: 20px;">
                            Ubah SK Penunjukan
                        </h5>
                        <p class="text-muted small mb-0 mt-1">Perbarui data surat keputusan di bawah ini.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 pb-4 pt-3">
                    <form id="formEditSk" method="POST" action="">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Jenis SK</label>
                                <select name="jenis" id="edit_sk_jenis" class="form-select" required>
                                    @foreach (\App\Http\Controllers\SkPenunjukanController::JENIS as $j)
                                        <option value="{{ $j }}">{{ $j }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Tahun</label>
                                <input type="number" name="tahun" id="edit_sk_tahun" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Nomor SK</label>
                                <input type="text" name="nomor" id="edit_sk_nomor" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal SK</label>
                                <input type="date" name="tanggal" id="edit_sk_tanggal" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Penerbit</label>
                                <input type="text" name="penerbit" id="edit_sk_penerbit" class="form-control" required>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-0 d-flex gap-2 justify-content-end" style="background: #f8fafc;">
                    <button type="button" class="btn-batal" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="formEditSk" class="btn-premium">
                        <i class="bi bi-save me-1"></i> Perbarui Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================= --}}
    {{-- MODAL EDIT PENYEDIA --}}
    {{-- ========================================= --}}
    <div class="modal fade" id="modalEditPenyedia" tabindex="-1" aria-labelledby="modalEditPenyediaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header border-0 pt-4 px-4 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="modalEditPenyediaLabel" style="color: var(--hijau-900); font-size: 20px;">
                            Ubah Penyedia
                        </h5>
                        <p class="text-muted small mb-0 mt-1">Perbarui data rekanan di bawah ini.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 pb-4 pt-3">
                    <form id="formEditPenyedia" method="POST" action="">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Nama Penyedia</label>
                                <input type="text" name="nama" id="edit_p_nama" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Alamat</label>
                                <input type="text" name="alamat" id="edit_p_alamat" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">NPWP</label>
                                <input type="text" name="npwp" id="edit_p_npwp" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Rekening Bank</label>
                                <input type="text" name="rekening_bank" id="edit_p_rekening" class="form-control">
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-0 d-flex gap-2 justify-content-end" style="background: #f8fafc;">
                    <button type="button" class="btn-batal" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="formEditPenyedia" class="btn-premium">
                        <i class="bi bi-save me-1"></i> Perbarui Data
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('partials.datatables-aset')
    <script>
        var kolom = @json($kolom).map(function (k) {
            return { data: k.data, className: k.kelas || '', orderable: k.urut !== false, searchable: k.cari !== false, defaultContent: '-' };
        });
        kolom.push({ data: 'aksi', orderable: false, searchable: false, className: 'text-end', defaultContent: '' });

        $('#tabel').DataTable({
            processing: true, 
            serverSide: true, 
            language: DT_BAHASA,
            ajax: @json($urlData),
            order: [[{{ $urutAwal ?? 0 }}, @json($urutArah ?? 'asc')]],
            columns: kolom,
            dom: '<"tabel-toolbar d-flex justify-content-between align-items-center"lf>t<"tabel-footer d-flex justify-content-between align-items-center"ip>',
            classes: {
                sFilterInput: "form-control ms-2",
                sLengthSelect: "form-select"
            }
        });

        // --- SCRIPT EDIT PEJABAT ---
        $(document).on('click', '.btn-edit[data-jabatan]', function(e) {
            e.preventDefault();
            $('#formEditPejabat').attr('action', "{{ route('pejabat.update', ':id') }}".replace(':id', $(this).data('id')));
            $('#edit_jabatan').val($(this).data('jabatan'));
            $('#edit_nama').val($(this).data('nama'));
            $('#edit_nip').val($(this).data('nip'));
            $('#edit_aktif_dari').val($(this).data('aktif_dari'));
            $('#edit_aktif_sampai').val($(this).data('aktif_sampai'));
            new bootstrap.Modal(document.getElementById('modalEditPejabat')).show();
        });

        // --- SCRIPT EDIT SK ---
        $(document).on('click', '.btn-edit[data-jenis]', function(e) {
            e.preventDefault();
            $('#formEditSk').attr('action', "{{ route('sk.update', ':id') }}".replace(':id', $(this).data('id')));
            $('#edit_sk_jenis').val($(this).data('jenis'));
            $('#edit_sk_nomor').val($(this).data('nomor'));
            $('#edit_sk_tanggal').val($(this).data('tanggal'));
            $('#edit_sk_penerbit').val($(this).data('penerbit'));
            $('#edit_sk_tahun').val($(this).data('tahun'));
            new bootstrap.Modal(document.getElementById('modalEditSk')).show();
        });

        // --- ERROR VALIDASI ---
        @if($errors->any())
            @if(old('_method') === 'PUT')
                @if(old('jenis'))
                    $('#formEditSk').attr('action', "{{ url()->current() }}/" + "{{ old('id') }}"); 
                    $('#edit_sk_jenis').val("{{ old('jenis') }}");
                    $('#edit_sk_nomor').val("{{ old('nomor') }}");
                    $('#edit_sk_tanggal').val("{{ old('tanggal') }}");
                    $('#edit_sk_penerbit').val("{{ old('penerbit') }}");
                    $('#edit_sk_tahun').val("{{ old('tahun') }}");
                    new bootstrap.Modal(document.getElementById('modalEditSk')).show();
                @else
                    $('#edit_jabatan').val("{{ old('jabatan') }}");
                    $('#edit_nama').val("{{ old('nama') }}");
                    $('#edit_nip').val("{{ old('nip') }}");
                    $('#edit_aktif_dari').val("{{ old('aktif_dari') }}");
                    $('#edit_aktif_sampai').val("{{ old('aktif_sampai') }}");
                    new bootstrap.Modal(document.getElementById('modalEditPejabat')).show();
                @endif
            @else
                new bootstrap.Modal(document.getElementById('modalTambahData')).show();
            @endif
        @endif

        // --- SCRIPT KONFIRMASI HAPUS ---
        $(document).on('submit', '.form-hapus', function(e) {
            e.preventDefault(); 
            var form = this;
            Swal.fire({
                title: 'Hapus Data?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="bi bi-trash3"></i> Ya, Hapus!',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'rounded-4',
                    confirmButton: 'btn-premium',
                    cancelButton: 'btn-batal'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); 
                }
            });
        });

        // --- SCRIPT EDIT PENYEDIA ---
        $(document).on('click', '.btn-edit[data-rekening_bank]', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            $('#formEditPenyedia').attr('action', "{{ route('penyedia.update', ':id') }}".replace(':id', id));
            $('#edit_p_nama').val($(this).data('nama'));
            $('#edit_p_alamat').val($(this).data('alamat'));
            $('#edit_p_npwp').val($(this).data('npwp'));
            $('#edit_p_rekening').val($(this).data('rekening_bank'));
            new bootstrap.Modal(document.getElementById('modalEditPenyedia')).show();
        });
    </script>
@endpush

{{-- Aset DataTables --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
    var DT_BAHASA = {
        search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', processing: 'Memuat data...',
        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data', infoEmpty: 'Tidak ada data',
        infoFiltered: '(disaring dari _MAX_ data)', zeroRecords: 'Data tidak ditemukan', emptyTable: 'Belum ada data',
        paginate: { first: 'Pertama', last: 'Terakhir', next: 'Berikutnya', previous: 'Sebelumnya' }
    };
</script>
<script>
    function rupiah(v) {
        if (v === null || v === undefined || v === '') return '-';
        return 'Rp' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(parseFloat(v));
    }
    function esc(t) {
        return String(t === null || t === undefined ? '' : t).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
</script>