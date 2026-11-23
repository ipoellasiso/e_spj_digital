@extends('layouts.app')

@section('title', 'Daftar pengguna')
@section('nav', 'pengguna')

@push('styles')
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endpush

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Daftar pengguna</h1>
            <p class="page-desc">Login memakai email atau NIP. Akun nonaktif tidak bisa masuk.</p>
        </div>
        <div>
            <a href="#" class="btn-premium" data-bs-toggle="modal" data-bs-target="#modalTambahPengguna">
                <i class="bi bi-plus-lg"></i> Tambah pengguna
            </a>
        </div>
    </div>

    <div class="card-tabel">
        <div class="p-0">
            <table id="tabel" class="tabel tabel-modern w-100" style="width:100%">
                <thead>
                <tr>
                    <th>Nama</th>
                    <th>NIP</th>
                    <th>Email</th>
                    <th>OPD</th>
                    <th>Peran</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- MODAL TAMBAH --}}
    <div class="modal fade" id="modalTambahPengguna" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header border-0 pt-4 px-4 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="modalTambahLabel" style="color: var(--hijau-900); font-size: 20px;">Tambah Pengguna</h5>
                        <p class="text-muted small mb-0 mt-1">Lengkapi data pengguna di bawah ini.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 pb-4 pt-3">
                    <form id="formTambahPengguna" method="POST" action="{{ route('pengguna.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Nama Lengkap</label>
                                <input type="text" name="name" class="form-control" placeholder="Nama lengkap pengguna" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="email@contoh.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">NIP <span class="text-muted fw-normal">(Opsional)</span></label>
                                <input type="text" name="nip" class="form-control" placeholder="18 digit NIP">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kata Sandi</label>
                                <input type="password" name="password" class="form-control" placeholder="Min. 8 karakter" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Konfirmasi Sandi</label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi kata sandi" required>
                            </div>
                            
                            <div class="col-md-12 mt-4"><hr class="border-light"></div>

                            @if(auth()->user()->isSuperadmin())
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">OPD</label>
                                <select name="opd_id" class="form-select">
                                    <option value="">-- Pilih OPD (Kosongkan jika Superadmin) --</option>
                                    @foreach(\App\Models\Opd::orderBy('nama')->get() as $opd)
                                        <option value="{{ $opd->id }}">{{ $opd->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <div class="col-md-12">
                                <label class="form-label fw-semibold d-block mb-2">Peran Pengguna</label>
                                @foreach(\App\Http\Controllers\PenggunaController::PERAN as $key => $val)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="peran[]" value="{{ $key }}" id="tambah_peran_{{ $key }}">
                                        <label class="form-check-label small text-dark" for="tambah_peran_{{ $key }}">{{ $val }}</label>
                                    </div>
                                @endforeach
                            </div>

                            <div class="col-md-12 mt-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="aktif" id="tambah_aktif" value="1" checked>
                                    <label class="form-check-label fw-semibold" for="tambah_aktif">Akun Aktif</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-0 d-flex gap-2 justify-content-end" style="background: #f8fafc;">
                    <button type="button" class="btn-batal" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="formTambahPengguna" class="btn-premium">
                        <i class="bi bi-save me-1"></i> Simpan Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT --}}
    <div class="modal fade" id="modalEditPengguna" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header border-0 pt-4 px-4 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="modalEditLabel" style="color: var(--hijau-900); font-size: 20px;">Ubah Data Pengguna</h5>
                        <p class="text-muted small mb-0 mt-1">Perbarui data pengguna di bawah ini.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 pb-4 pt-3">
                    <form id="formEditPengguna" method="POST" action="">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Nama Lengkap</label>
                                <input type="text" name="name" id="edit_user_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" id="edit_user_email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">NIP <span class="text-muted fw-normal">(Opsional)</span></label>
                                <input type="text" name="nip" id="edit_user_nip" class="form-control">
                            </div>
                            
                            <div class="col-md-12 mt-4"><hr class="border-light"></div>

                            @if(auth()->user()->isSuperadmin())
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">OPD</label>
                                <select name="opd_id" id="edit_user_opd_id" class="form-select">
                                    <option value="">-- Pilih OPD (Kosongkan jika Superadmin) --</option>
                                    @foreach(\App\Models\Opd::orderBy('nama')->get() as $opd)
                                        <option value="{{ $opd->id }}">{{ $opd->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <div class="col-md-12">
                                <label class="form-label fw-semibold d-block mb-2">Peran Pengguna</label>
                                @foreach(\App\Http\Controllers\PenggunaController::PERAN as $key => $val)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="peran[]" value="{{ $key }}" id="edit_peran_{{ $key }}">
                                        <label class="form-check-label small text-dark" for="edit_peran_{{ $key }}">{{ $val }}</label>
                                    </div>
                                @endforeach
                            </div>

                            <div class="col-md-12 mt-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="aktif" id="edit_user_aktif" value="1">
                                    <label class="form-check-label fw-semibold" for="edit_user_aktif">Akun Aktif</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-0 d-flex gap-2 justify-content-end" style="background: #f8fafc;">
                    <button type="button" class="btn-batal" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="formEditPengguna" class="btn-premium">
                        <i class="bi bi-save me-1"></i> Perbarui Data
                    </button>
                </div>
            </div>
        </div>
    </div>

        {{-- MODAL RESET PASSWORD --}}
    <div class="modal fade" id="modalResetPassword" tabindex="-1" aria-labelledby="modalResetLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                
                <div class="modal-header border-0 pt-4 px-4 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="modalResetLabel" style="color: var(--hijau-900); font-size: 20px;">
                            Reset Kata Sandi
                        </h5>
                        <p class="text-muted small mb-0 mt-1">Untuk pengguna: <strong id="reset_nama_user" class="text-dark"></strong></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 pb-4 pt-3">
                    <!-- Tambahkan action kosong dan method POST (biarkan JS yang mengisi URL-nya) -->
                    <form id="formResetPassword" method="POST" action="">
                        @csrf
                        
                        {{-- Tambahkan input hidden manual ini sebagai cadangan --}}
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kata Sandi Baru</label>
                            <input type="password" name="password_baru" class="form-control" placeholder="Min. 8 karakter" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Konfirmasi Sandi Baru</label>
                            <input type="password" name="password_baru_confirmation" class="form-control" placeholder="Ulangi sandi baru" required>
                        </div>
                    </form>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-0 d-flex gap-2 justify-content-end" style="background: #f8fafc;">
                    <button type="button" class="btn-batal" data-bs-dismiss="modal">Batal</button>
                    
                    <!-- PERHATIKAN ATRIBUT form="formResetPassword" -->
                    <button type="submit" form="formResetPassword" class="btn-premium bg-warning border-warning text-dark">
                        <i class="bi bi-key-fill me-1"></i> Reset Sandi
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
            ajax: "{{ route('pengguna.data') }}",
            order: [[0, 'asc']],
            dom: '<"tabel-toolbar d-flex justify-content-between align-items-center"lf>t<"tabel-footer d-flex justify-content-between align-items-center"ip>',
            classes: {
                sFilterInput: "form-control ms-2",
                sLengthSelect: "form-select"
            },
            columns: [
                { data: 'name', className: 'fw-bold text-dark' },
                { data: 'nip', className: 'angka', defaultContent: '-' },
                { data: 'email', className: 'text-primary' },
                { data: 'opd', orderable: false, searchable: false },
                { data: 'peran', orderable: false, searchable: false, render: function(data){ 
                    if(!data) return '-';
                    return data.split(', ').map(p => `<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-normal">${p}</span>`).join(' ');
                }},
                { data: 'aktif', searchable: false, className: 'text-center' },
                { data: 'aksi', orderable: false, searchable: false, className: 'text-end', defaultContent: '' }
            ]
        });

        // --- SCRIPT EDIT PENGGUNA (DIPERBAIKI) ---
        $(document).on('click', '.btn-edit', function(e) {
            e.preventDefault();
            
            var id = $(this).data('id');
            $('#formEditPengguna').attr('action', "{{ route('pengguna.update', ':id') }}".replace(':id', id));
            
            // Isi input text & select
            $('#edit_user_name').val($(this).data('name'));
            $('#edit_user_email').val($(this).data('email'));
            $('#edit_user_nip').val($(this).data('nip'));
            $('#edit_user_opd_id').val($(this).data('opd_id'));
            $('#edit_user_aktif').prop('checked', $(this).data('aktif') == 1);

            // Reset semua checkbox peran
            $('#formEditPengguna input[name="peran[]"]').prop('checked', false);

            // Ambil data raw_roles dari baris DataTables (bukan dari atribut HTML)
            var rowData = tabel.row($(this).closest('tr')).data();
            var roles = rowData.raw_roles; 
            
            // Centang checkbox yang sesuai
            if (roles && Array.isArray(roles)) {
                roles.forEach(function(role) {
                    $('#edit_peran_' + role).prop('checked', true);
                });
            }
            
            new bootstrap.Modal(document.getElementById('modalEditPengguna')).show();
        });

        // --- SCRIPT KONFIRMASI HAPUS (SWEETALERT) ---
        $(document).on('submit', '.form-hapus', function(e) {
            e.preventDefault(); 
            var form = this;
            Swal.fire({
                title: 'Hapus Pengguna?',
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

        // --- SCRIPT RESET PASSWORD ---
        $(document).on('click', '.btn-reset-pass', function(e) {
            e.preventDefault();
            
            var id = $(this).data('id');
            var nama = $(this).data('name');
            
            // Set Action URL Form
            $('#formResetPassword').attr('action', "{{ route('pengguna.reset-password', ':id') }}".replace(':id', id));
            
            // Set Teks Nama User di Modal
            $('#reset_nama_user').text(nama);
            
            // Reset isi form
            // $('#formResetPassword input').val('');

            // Tampilkan Modal
            new bootstrap.Modal(document.getElementById('modalResetPassword')).show();
        });
    </script>
@endpush