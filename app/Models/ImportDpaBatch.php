<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOpd;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportDpaBatch extends Model
{
    use BelongsToOpd;

    protected $table = 'import_dpa_batch';
    protected $guarded = [];
    protected $casts = ['catatan' => 'array', 'total_pagu' => 'decimal:2'];

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function rows(): HasMany
    {
        return $this->hasMany(ImportDpaRow::class, 'batch_id');
    }
}