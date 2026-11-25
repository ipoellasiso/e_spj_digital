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

    public function dpaVersi(): BelongsTo
    {
        // Sesuaikan nama Model dan foreign key-nya dengan struktur database kamu
        // Contoh: Jika tabel anggaran_rekening punya kolom 'dpa_versi_id'
        return $this->belongsTo(DpaVersi::class, 'dpa_versi_id');
    }

    /** Sisa pagu = pagu - realisasi final - reserved (SPJ yang sedang berproses). */
    public function getSisaAttribute(): string
    {
        return number_format((float) $this->pagu - (float) $this->realisasi_final - (float) $this->reserved, 2, '.', '');
    }
}