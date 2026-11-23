<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOpd;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use BelongsToOpd;

    protected $table = 'program';
    public $timestamps = false;
    protected $guarded = [];

    public function kegiatan(): HasMany
    {
        return $this->hasMany(Kegiatan::class);
    }
}