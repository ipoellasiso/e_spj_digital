<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOpd;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnggaranRekening extends Model
{
    use BelongsToOpd;

    protected $table = 'anggaran_rekening';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['pagu' => 'decimal:2', 'realisasi_final' => 'decimal:2', 'reserved' => 'decimal:2'];

    public function subKegiatan(): BelongsTo
    {
        return $this->belongsTo(SubKegiatan::class);
    }

    public function rekening(): BelongsTo
    {
        return $this->belongsTo(Rekening::class);
    }

    /** Sisa pagu = pagu - realisasi final - reserved (SPJ yang sedang berproses). */
    public function getSisaAttribute(): string
    {
        return number_format((float) $this->pagu - (float) $this->realisasi_final - (float) $this->reserved, 2, '.', '');
    }
}