@extends('layouts.app')

@section('title', 'Ganti Kata Sandi')
@section('nav', 'profil')

@section('content')
    <div class="form-wrapper">
        {{-- Tombol Kembali & Judul --}}
        <div>
            <a href="{{ url()->previous() }}" class="back-link">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <h1 class="form-title">Ganti Kata Sandi</h1>
        </div>

        {{-- Card Form --}}
        <form method="POST" action="{{ route('profil.password.update') }}" class="form-card">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="password_lama" class="form-label fw-semibold">Kata Sandi Saat Ini</label>
                <input type="password" name="password_lama" id="password_lama" class="form-control @error('password_lama') is-invalid @enderror" placeholder="Masukkan kata sandi lama" required>
                @error('password_lama')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label for="password_baru" class="form-label fw-semibold">Kata Sandi Baru</label>
                <input type="password" name="password_baru" id="password_baru" class="form-control @error('password_baru') is-invalid @enderror" placeholder="Minimal 8 karakter" required>
                @error('password_baru')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label for="password_baru_confirmation" class="form-label fw-semibold">Ulangi Kata Sandi Baru</label>
                <input type="password" name="password_baru_confirmation" id="password_baru_confirmation" class="form-control" placeholder="Ketik ulang kata sandi baru" required>
            </div>

            <div class="d-flex gap-3 pt-4 border-top" style="border-color: var(--border-color) !important;">
                <button type="submit" class="btn-premium">
                    <i class="bi bi-shield-lock me-1"></i> Simpan Kata Sandi
                </button>
                <a href="{{ url()->previous() }}" class="btn-batal">Batal</a>
            </div>
        </form>
    </div>
@endsection