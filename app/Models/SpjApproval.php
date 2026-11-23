<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpjApproval extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'spj_approval';
    
    // HANYA PAKAI FILLABLE, HAPUS GUARDED
    protected $fillable = [
        'spj_id',
        'user_id',
        'tahap',
        'aksi',
        'catatan',
    ];
    
    // protected $guarded = []; // <-- HAPUS BARIS INI

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}