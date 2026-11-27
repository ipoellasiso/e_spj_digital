<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paket extends Model
{
    use HasFactory;

    // Tambahkan fillable agar bisa diisi massal
    protected $fillable = [
        'nama',
        'harga_bulanan',
        'harga_tahunan',
        'fitur',
        'is_populer',
    ];

    // Casting fitur menjadi array/json agar mudah di-looping di view
    protected $casts = [
        'fitur' => 'array',
        'is_populer' => 'boolean',
    ];

    // Relasi: Satu paket bisa punya banyak tagihan
    public function tagihans()
    {
        return $this->hasMany(Tagihan::class, 'paket_id');
    }
}