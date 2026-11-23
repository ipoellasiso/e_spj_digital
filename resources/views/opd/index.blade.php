@extends('layouts.app')

@section('title', 'Daftar OPD')
@section('nav', 'opd')

@push('styles')
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Daftar OPD</h1>
            <p class="page-desc">Kode OPD harus sama dengan kode unit di file Excel DPA. Singkatan dipakai pada nomor dokumen.</p>
        </div>
        <div>
            <a href="#" class="btn-premium" data-bs-toggle="modal" data-bs-target="#modalTambahOpd">
                <i class="bi bi-plus-lg"></i> Tambah OPD
            </a>
        </div>
    </div>

    <div class="card-tabel">
        <div class="p-0">
            <table id="tabel" class="tabel tabel-modern w-100" style="width:100%">
                <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Singkatan</th>
                    <th>Logo</th>
                    <th class="text-end">Aksi</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- ========================================= --}}
    {{-- MODAL TAMBAH OPD --}}
    {{-- ========================================= --}}
    <div class="modal fade" id="modalTambahOpd" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                
                <div class="modal-header border-0 pt-4 px-4 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="modalTambahLabel" style="color: var(--hijau-900); font-size: 20px;">
                            Tambah OPD
                        </h5>
                        <p class="text-muted small mb-0 mt-1">Lengkapi informasi OPD di bawah ini.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 pb-4 pt-3">
                    <form id="formTambahOpd" method="POST" action="{{ route('opd.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Kode OPD</label>
                                <input type="text" name="kode" class="form-control" placeholder="Contoh: 7.01.0.00.0.00.00.0000" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Nama OPD</label>
                                <input type="text" name="nama" class="form-control" placeholder="Nama lengkap instansi" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Singkatan</label>
                                <input type="text" name="singkatan" class="form-control" placeholder="Contoh: TAWAELI" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Alamat <span class="fw-normal text-muted">(Opsional)</span></label>
                                <input type="text" name="alamat" class="form-control" placeholder="Alamat kantor">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Logo Instansi <span class="fw-normal text-muted">(Opsional)</span></label>
                                <input type="file" name="logo" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-0 d-flex gap-2 justify-content-end" style="background: #f8fafc;">
                    <button type="button" class="btn-batal" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="formTambahOpd" class="btn-premium">
                        <i class="bi bi-save me-1"></i> Simpan Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================= --}}
    {{-- MODAL EDIT OPD --}}
    {{-- ========================================= --}}
    <div class="modal fade" id="modalEditOpd" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                
                <div class="modal-header border-0 pt-4 px-4 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="modalEditLabel" style="color: var(--hijau-900); font-size: 20px;">
                            Ubah Data OPD
                        </h5>
                        <p class="text-muted small mb-0 mt-1">Perbarui informasi OPD di bawah ini.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 pb-4 pt-3">
                    <form id="formEditOpd" method="POST" action="" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Kode OPD</label>
                                <input type="text" name="kode" id="edit_opd_kode" class="form-control" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Nama OPD</label>
                                <input type="text" name="nama" id="edit_opd_nama" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Singkatan</label>
                                <input type="text" name="singkatan" id="edit_opd_singkatan" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Alamat</label>
                                <input type="text" name="alamat" id="edit_opd_alamat" class="form-control">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Ganti Logo <span class="fw-normal text-muted">(Opsional)</span></label>
                                <input type="file" name="logo" class="form-control" accept="image/*">
                                <div class="form-text">Biarkan kosong jika tidak ingin mengubah logo.</div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-0 d-flex gap-2 justify-content-end" style="background: #f8fafc;">
                    <button type="button" class="btn-batal" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="formEditOpd" class="btn-premium">
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
        var tabel = $('#tabel').DataTable({
            processing: true, 
            serverSide: true, 
            language: DT_BAHASA,
            ajax: "{{ route('opd.data') }}",
            order: [[0, 'asc']],
            dom: '<"tabel-toolbar d-flex justify-content-between align-items-center"lf>t<"tabel-footer d-flex justify-content-between align-items-center"ip>',
            classes: {
                sFilterInput: "form-control ms-2",
                sLengthSelect: "form-select"
            },
            columns: [
                { data: 'kode', className: 'angka fw-semibold text-primary' },
                { data: 'nama', className: 'fw-medium' },
                { data: 'singkatan', className: 'angka' },
                { data: 'logo', orderable: false, searchable: false },
                { data: 'aksi', orderable: false, searchable: false, className: 'text-end', defaultContent: '' }
            ]
        });

        // --- SCRIPT EDIT OPD ---
        $(document).on('click', '.btn-edit', function(e) {
            e.preventDefault();
            
            var id = $(this).data('id');
            $('#formEditOpd').attr('action', "{{ route('opd.update', ':id') }}".replace(':id', id));
            
            $('#edit_opd_kode').val($(this).data('kode'));
            $('#edit_opd_nama').val($(this).data('nama'));
            $('#edit_opd_singkatan').val($(this).data('singkatan'));
            $('#edit_opd_alamat').val($(this).data('alamat'));

            new bootstrap.Modal(document.getElementById('modalEditOpd')).show();
        });

        // --- SCRIPT KONFIRMASI HAPUS (SWEETALERT) ---
        $(document).on('submit', '.form-hapus', function(e) {
            e.preventDefault(); 
            var form = this;
            Swal.fire({
                title: 'Hapus OPD?',
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
                if (result.isConfirmed) form.submit();
            });
        });
    </script>
@endpush