@extends('layouts.app')

@section('title', $sk->exists ? 'Ubah SK penunjukan' : 'Tambah SK penunjukan')
@section('nav', 'sk')

@section('content')
    <div class="mb-4">
        <a href="{{ route('sk.index') }}" class="small"><i class="bi bi-arrow-left"></i> SK penunjukan</a>
        <h1 class="h4 fw-bold mt-1" style="color:var(--hutan)">{{ $sk->exists ? 'Ubah SK penunjukan' : 'Tambah SK penunjukan' }}</h1>
    </div>

    <form method="POST" novalidate class="panel p-4" style="max-width:720px"
          action="{{ $sk->exists ? route('sk.update', $sk) : route('sk.store') }}">
        @csrf
        @if ($sk->exists) @method('PUT') @endif

        @unless ($sk->exists) @include('partials.pilih-opd') @endunless

        <div class="mb-3">
            <label for="jenis" class="form-label fw-semibold">Jenis</label>
            <select id="jenis" name="jenis" class="form-select @error('jenis') is-invalid @enderror">
                <option value="">Pilih jenis</option>
                @foreach ($jenis as $j)
                    <option value="{{ $j }}" @selected(old('jenis', $sk->jenis) === $j)>{{ $j }}</option>
                @endforeach
            </select>
            @error('jenis')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="nomor" class="form-label fw-semibold">Nomor SK</label>
            <input id="nomor" name="nomor" value="{{ old('nomor', $sk->nomor) }}" placeholder="900.1/02-01/I/TWL/2026" required
                   class="form-control angka @error('nomor') is-invalid @enderror">
            @error('nomor')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="tanggal" class="form-label fw-semibold">Tanggal SK</label>
                <input id="tanggal" name="tanggal" type="date" value="{{ old('tanggal', $sk->tanggal?->format('Y-m-d')) }}"
                       class="form-control @error('tanggal') is-invalid @enderror">
                @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="tahun" class="form-label fw-semibold">Tahun anggaran</label>
                <input id="tahun" name="tahun" type="number" min="2000" max="2100" value="{{ old('tahun', $sk->tahun) }}"
                       class="form-control @error('tahun') is-invalid @enderror">
                @error('tahun')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mb-4">
            <label for="penerbit" class="form-label fw-semibold">Penerbit</label>
            <input id="penerbit" name="penerbit" value="{{ old('penerbit', $sk->penerbit) }}" placeholder="Kepala Kecamatan Tawaeli" required
                   class="form-control @error('penerbit') is-invalid @enderror">
            @error('penerbit')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Tulis sesuai yang tertera di SK, misalnya Walikota Palu atau Kepala Kecamatan Tawaeli.</div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-kuning">Simpan</button>
            <a href="{{ route('sk.index') }}" class="btn btn-light">Batal</a>
        </div>
    </form>
@endsection