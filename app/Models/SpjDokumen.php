<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpjDokumen extends Model
{
    protected $table = 'spj_dokumen';
    protected $guarded = [];
    protected $casts = ['snapshot' => 'array', 'tanggal' => 'date'];

    public function spj(): BelongsTo
    {
        return $this->belongsTo(Spj::class);
    }

    /** Dokumen yang dirujuk (BAST merujuk BA pemeriksaan; yang lain merujuk nota pesanan). */
    public function induk(): BelongsTo
    {
        return $this->belongsTo(self::class, 'induk_dokumen_id');
    }
}