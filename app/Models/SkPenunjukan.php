<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOpd;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkPenunjukan extends Model
{
    use BelongsToOpd;

    protected $table = 'sk_penunjukan';
    protected $guarded = [];
    protected $casts = ['tanggal' => 'date'];

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }
}