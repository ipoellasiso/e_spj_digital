@extends('layouts.app')

@section('title', $opd->exists ? 'Ubah OPD' : 'Tambah OPD')
@section('nav', 'opd')

@section('content')
    <div class="mb-4">
        <a href="{{ route('opd.index') }}" class="small"><i class="bi bi-arrow-left"></i> Daftar OPD</a>
        <h1 class="h4 fw-bold mt-1" style="color:var(--hutan)">{{ $opd->exists ? 'Ubah OPD' : 'Tambah OPD' }}</h1>
    </div>

    <form method="POST" enctype="multipart/form-data" novalidate
          action="{{ $opd->exists ? route('opd.update', $opd) : route('opd.store') }}" class="panel p-4" style="max-width:720px">
        @csrf
        @if ($opd->exists) @method('PUT') @endif

        <div class="mb-3">
            <label for="kode" class="form-label fw-semibold">Kode OPD</label>
            <input id="kode" name="kode" value="{{ old('kode', $opd->kode) }}" required placeholder="1.05.0.00.0.00.01.0000"
                   class="form-control angka @error('kode') is-invalid @enderror">
            @error('kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Angka dipisah titik, persis seperti kode unit di file Excel DPA.</div>
        </div>

        <div class="mb-3">
            <label for="nama" class="form-label fw-semibold">Nama OPD</label>
            <input id="nama" name="nama" value="{{ old('nama', $opd->nama) }}" required class="form-control @error('nama') is-invalid @enderror">
            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="singkatan" class="form-label fw-semibold">Singkatan</label>
            <input id="singkatan" name="singkatan" value="{{ old('singkatan', $opd->singkatan) }}" required placeholder="TAWAELI"
                   class="form-control @error('singkatan') is-invalid @enderror">
            @error('singkatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Huruf besar, angka, atau tanda hubung. Contoh pemakaian: 0263/NP/<b>TAWAELI</b>/Apr/2026.</div>
        </div>

        <div class="mb-3">
            <label for="alamat" class="form-label fw-semibold">Alamat (untuk kop dokumen)</label>
            <input id="alamat" name="alamat" value="{{ old('alamat', $opd->alamat) }}" class="form-control @error('alamat') is-invalid @enderror">
            @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-4">
            <label for="logo" class="form-label fw-semibold">Logo (untuk kop dokumen)</label>
            @if ($opd->logo_path)
                <div class="mb-2"><img src="{{ Storage::disk('public')->url($opd->logo_path) }}" alt="Logo saat ini" height="56"></div>
            @endif
            <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp" class="form-control @error('logo') is-invalid @enderror">
            @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">PNG, JPG, atau WebP, maksimal 1 MB. Kosongkan bila tidak ingin mengganti.</div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-kuning">Simpan</button>
            <a href="{{ route('opd.index') }}" class="btn btn-light">Batal</a>
        </div>
    </form>
@endsection