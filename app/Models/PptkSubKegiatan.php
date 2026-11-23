<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOpd;
use Illuminate\Database\Eloquent\Model;

class PptkSubKegiatan extends Model
{
    use BelongsToOpd;

    protected $table = 'pptk_sub_kegiatan';
    public $timestamps = false;
    protected $guarded = [];
}