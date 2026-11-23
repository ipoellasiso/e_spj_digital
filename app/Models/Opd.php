<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opd extends Model
{
    protected $table = 'opd';
    protected $guarded = [];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function pejabat(): HasMany
    {
        return $this->hasMany(PejabatOpd::class);
    }

    public function dpaVersi(): HasMany
    {
        return $this->hasMany(DpaVersi::class);
    }
}