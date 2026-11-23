@extends('layouts.app')

@section('title', $penyedia->exists ? 'Ubah penyedia' : 'Tambah penyedia')
@section('nav', 'penyedia')

@section('content')
    <div class="mb-4">
        <a href="{{ route('penyedia.index') }}" class="small"><i class="bi bi-arrow-left"></i> Penyedia</a>
        <h1 class="h4 fw-bold mt-1" style="color:var(--hutan)">{{ $penyedia->exists ? 'Ubah penyedia' : 'Tambah penyedia' }}</h1>
    </div>

    <form method="POST" novalidate class="panel p-4" style="max-width:720px"
          action="{{ $penyedia->exists ? route('penyedia.update', $penyedia) : route('penyedia.store') }}">
        @csrf
        @if ($penyedia->exists) @method('PUT') @endif

        @unless ($penyedia->exists) @include('partials.pilih-opd') @endunless

        <div class="mb-3">
            <label for="nama" class="form-label fw-semibold">Nama penyedia</label>
            <input id="nama" name="nama" value="{{ old('nama', $penyedia->nama) }}" required class="form-control @error('nama') is-invalid @enderror">
            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="alamat" class="form-label fw-semibold">Alamat</label>
            <input id="alamat" name="alamat" value="{{ old('alamat', $penyedia->alamat) }}" class="form-control @error('alamat') is-invalid @enderror">
            @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label for="npwp" class="form-label fw-semibold">NPWP</label>
                <input id="npwp" name="npwp" value="{{ old('npwp', $penyedia->npwp) }}" inputmode="numeric" placeholder="15 atau 16 digit"
                       class="form-control angka @error('npwp') is-invalid @enderror">
                @error('npwp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Titik dan tanda hubung boleh diketik; disimpan sebagai angka.</div>
            </div>
            <div class="col-md-6">
                <label for="rekening_bank" class="form-label fw-semibold">Rekening bank</label>
                <input id="rekening_bank" name="rekening_bank" value="{{ old('rekening_bank', $penyedia->rekening_bank) }}"
                       placeholder="Bank, nomor rekening, atas nama" class="form-control @error('rekening_bank') is-invalid @enderror">
                @error('rekening_bank')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-kuning">Simpan</button>
            <a href="{{ route('penyedia.index') }}" class="btn btn-light">Batal</a>
        </div>
    </form>
@endsection