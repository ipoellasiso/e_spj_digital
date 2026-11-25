<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Opd extends Model
{
    protected $table = 'opd';
    protected $guarded = [];

    // Casting agar tanggal_berakhir otomatis menjadi objek Carbon (tanggal)
    protected $casts = [
        'tanggal_berakhir' => 'date',
    ];

    // Relasi yang sudah ada sebelumnya
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function pejabat(): HasMany
    {
        return $this->hasMany(PejabatOpd::class);
    }

    public function dpaVersi(): HasMany
    {
        return $this->hasMany(DpaVersi::class);
    }

    // --- TAMBAHAN UNTUK LANGGANAN ---

    // Relasi: OPD punya banyak riwayat tagihan
    public function tagihans(): HasMany
    {
        return $this->hasMany(Tagihan::class, 'opd_id');
    }

    // Relasi: OPD sedang berlangganan paket apa
    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class, 'paket_id');
    }
}