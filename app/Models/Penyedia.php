<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOpd;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penyedia extends Model
{
    use BelongsToOpd;

    protected $table = 'penyedia';
    protected $guarded = [];

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }
}