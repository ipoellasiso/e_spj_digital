<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOpd;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubKegiatan extends Model
{
    use BelongsToOpd;

    protected $table = 'sub_kegiatan';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['pagu' => 'decimal:2'];

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function anggaranRekening(): HasMany
    {
        return $this->hasMany(AnggaranRekening::class);
    }
}