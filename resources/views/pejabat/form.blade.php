@extends('layouts.app')

@section('title', $pejabat->exists ? 'Ubah pejabat' : 'Tambah pejabat')
@section('nav', 'pejabat')

@section('content')
    <div class="mb-4">
        <a href="{{ route('pejabat.index') }}" class="small"><i class="bi bi-arrow-left"></i> Pejabat penandatangan</a>
        <h1 class="h4 fw-bold mt-1" style="color:var(--hutan)">{{ $pejabat->exists ? 'Ubah pejabat' : 'Tambah pejabat' }}</h1>
    </div>

    <form method="POST" novalidate class="panel p-4" style="max-width:720px"
          action="{{ $pejabat->exists ? route('pejabat.update', $pejabat) : route('pejabat.store') }}">
        @csrf
        @if ($pejabat->exists) @method('PUT') @endif

        @unless ($pejabat->exists) @include('partials.pilih-opd') @endunless

        <div class="mb-3">
            <label for="jabatan" class="form-label fw-semibold">Jabatan</label>
            <select id="jabatan" name="jabatan" class="form-select @error('jabatan') is-invalid @enderror">
                <option value="">Pilih jabatan</option>
                @foreach (\App\Models\PejabatOpd::JABATAN as $j)
                    <option value="{{ $j }}" @selected(old('jabatan', $pejabat->jabatan) === $j)>{{ $j }}</option>
                @endforeach
            </select>
            @error('jabatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="jabatan_struktural" class="form-label fw-semibold">Jabatan struktural <span class="text-secondary fw-normal">(opsional)</span></label>
            <input id="jabatan_struktural" name="jabatan_struktural" value="{{ old('jabatan_struktural', $pejabat->jabatan_struktural) }}"
                   placeholder="Kepala Kecamatan Tawaeli" class="form-control @error('jabatan_struktural') is-invalid @enderror">
            @error('jabatan_struktural')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Tercetak di Nota Pesanan (kolom Jabatan) dan Kwitansi (Sudah terima dari). Kosongkan bila sama dengan jabatan di atas.</div>
        </div>

        <div class="mb-3">
            <label for="nama" class="form-label fw-semibold">Nama lengkap dengan gelar</label>
            <input id="nama" name="nama" value="{{ old('nama', $pejabat->nama) }}" required class="form-control @error('nama') is-invalid @enderror">
            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="nip" class="form-label fw-semibold">NIP</label>
            <input id="nip" name="nip" value="{{ old('nip', $pejabat->nip) }}" inputmode="numeric" placeholder="18 digit, boleh dengan spasi"
                   class="form-control angka @error('nip') is-invalid @enderror">
            @error('nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Kosongkan bila tidak ada NIP.</div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label for="aktif_dari" class="form-label fw-semibold">Mulai menjabat</label>
                <input id="aktif_dari" name="aktif_dari" type="date" value="{{ old('aktif_dari', $pejabat->aktif_dari?->format('Y-m-d')) }}"
                       class="form-control @error('aktif_dari') is-invalid @enderror">
                @error('aktif_dari')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="aktif_sampai" class="form-label fw-semibold">Selesai menjabat</label>
                <input id="aktif_sampai" name="aktif_sampai" type="date" value="{{ old('aktif_sampai', $pejabat->aktif_sampai?->format('Y-m-d')) }}"
                       class="form-control @error('aktif_sampai') is-invalid @enderror">
                @error('aktif_sampai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Kosongkan bila masih menjabat.</div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-kuning">Simpan</button>
            <a href="{{ route('pejabat.index') }}" class="btn btn-light">Batal</a>
        </div>
    </form>
@endsection