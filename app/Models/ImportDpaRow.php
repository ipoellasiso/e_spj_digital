<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportDpaRow extends Model
{
    protected $table = 'import_dpa_row';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['pagu' => 'decimal:2'];
}