@extends('layouts.app')

@section('title', $user->exists ? 'Ubah pengguna' : 'Tambah pengguna')
@section('nav', 'pengguna')

@section('content')
    <div class="mb-4">
        <a href="{{ route('pengguna.index') }}" class="small"><i class="bi bi-arrow-left"></i> Daftar pengguna</a>
        <h1 class="h4 fw-bold mt-1" style="color:var(--hutan)">{{ $user->exists ? 'Ubah pengguna' : 'Tambah pengguna' }}</h1>
    </div>

    <form method="POST" novalidate class="panel p-4" style="max-width:720px"
          action="{{ $user->exists ? route('pengguna.update', $user) : route('pengguna.store') }}">
        @csrf
        @if ($user->exists) @method('PUT') @endif

        <div class="mb-3">
            <label for="name" class="form-label fw-semibold">Nama</label>
            <input id="name" name="name" value="{{ old('name', $user->name) }}" required class="form-control @error('name') is-invalid @enderror">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="email" class="form-label fw-semibold">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="off"
                       class="form-control @error('email') is-invalid @enderror">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="nip" class="form-label fw-semibold">NIP</label>
                <input id="nip" name="nip" value="{{ old('nip', $user->nip) }}" inputmode="numeric" placeholder="18 digit, boleh dengan spasi"
                       class="form-control angka @error('nip') is-invalid @enderror">
                @error('nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="password" class="form-label fw-semibold">Kata sandi {{ $user->exists ? '(kosongkan bila tidak diganti)' : '' }}</label>
                <input id="password" name="password" type="password" autocomplete="new-password" {{ $user->exists ? '' : 'required' }}
                       class="form-control @error('password') is-invalid @enderror">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Minimal 8 karakter.</div>
            </div>
            <div class="col-md-6">
                <label for="password_confirmation" class="form-label fw-semibold">Ulangi kata sandi</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="form-control">
            </div>
        </div>

        <div class="mb-3">
            <label for="opd_id" class="form-label fw-semibold">OPD</label>
            @if ($opdSendiri)
                <input class="form-control" value="{{ $opdSendiri }}" disabled>
                <div class="form-text">Pengguna baru otomatis masuk ke OPD Anda.</div>
            @else
                <select id="opd_id" name="opd_id" class="form-select @error('opd_id') is-invalid @enderror">
                    <option value="">Pilih OPD</option>
                    @foreach ($daftarOpd as $o)
                        <option value="{{ $o->id }}" @selected((string) old('opd_id', $user->opd_id) === (string) $o->id)>{{ $o->nama }}</option>
                    @endforeach
                </select>
                @error('opd_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Tidak berlaku untuk superadmin.</div>
            @endif
        </div>

        <div class="mb-3">
            <div class="form-label fw-semibold">Peran</div>
            @foreach ($peranBoleh as $kode => $label)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="peran[]" id="peran-{{ $kode }}" value="{{ $kode }}"
                           @checked(in_array($kode, (array) $peranTerpilih, true))>
                    <label class="form-check-label" for="peran-{{ $kode }}">{{ $label }}</label>
                </div>
            @endforeach
            @error('peran')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            @error('peran.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="form-check form-switch mb-4">
            <input class="form-check-input" type="checkbox" role="switch" name="aktif" id="aktif" value="1" @checked(old('aktif', $user->aktif ?? true))>
            <label class="form-check-label" for="aktif">Akun aktif</label>
            @error('aktif')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-kuning">Simpan</button>
            <a href="{{ route('pengguna.index') }}" class="btn btn-light">Batal</a>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        (function () {
            var cb = document.getElementById('peran-superadmin'), sel = document.getElementById('opd_id');
            if (!cb || !sel) return;
            function atur() { sel.disabled = cb.checked; if (cb.checked) sel.value = ''; }
            cb.addEventListener('change', atur); atur();
        })();
    </script>
@endpush