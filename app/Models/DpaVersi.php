<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOpd;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DpaVersi extends Model
{
    use BelongsToOpd;

    protected $table = 'dpa_versi';
    protected $guarded = [];
    // aktif: true = versi aktif, null = tidak aktif (lihat catatan di migration)
    protected $casts = ['aktif' => 'boolean'];

    public function scopeAktif(Builder $q): Builder
    {
        return $q->where('aktif', true);
    }

    public function program(): HasMany
    {
        return $this->hasMany(Program::class);
    }

    public function subKegiatan(): HasMany
    {
        return $this->hasMany(SubKegiatan::class);
    }

    public function anggaranRekening(): HasMany
    {
        return $this->hasMany(AnggaranRekening::class);
    }
}