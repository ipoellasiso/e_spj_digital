<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpjItem extends Model
{
    protected $table = 'spj_item';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['volume' => 'decimal:2', 'harga_satuan' => 'decimal:2', 'jumlah' => 'decimal:2', 'pajak' => 'decimal:2'];
}