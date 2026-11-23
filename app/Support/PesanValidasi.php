<?php

namespace App\Support;

/** Pesan validasi berbahasa Indonesia yang berlaku untuk semua aturan umum. */
class PesanValidasi
{
    public const UMUM = [
        'required' => ':attribute wajib diisi.',
        'required_if' => ':attribute wajib diisi.',
        'unique' => ':attribute sudah dipakai.',
        'regex' => 'Format :attribute tidak valid.',
        'email' => 'Format :attribute tidak valid.',
        'max' => ':attribute terlalu panjang (maksimal :max).',
        'min' => ':attribute minimal :min karakter.',
        'confirmed' => 'Konfirmasi :attribute tidak sama.',
        'in' => ':attribute tidak diizinkan.',
        'exists' => ':attribute tidak ditemukan.',
        'image' => ':attribute harus berupa gambar.',
        'mimes' => ':attribute harus berformat :values.',
        'array' => ':attribute tidak valid.',
        'date' => ':attribute bukan tanggal yang valid.',
        'after_or_equal' => ':attribute tidak boleh sebelum tanggal mulai.',
        'integer' => ':attribute harus berupa angka.',
        'between' => ':attribute tidak valid.',
    ];
}