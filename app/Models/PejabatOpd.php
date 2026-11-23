<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOpd;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PejabatOpd extends Model
{
    use BelongsToOpd;

    protected $table = 'pejabat_opd';
    protected $guarded = [];
    protected $casts = ['aktif_dari' => 'date', 'aktif_sampai' => 'date'];

    public const JABATAN = [
        'PA', 'KPA', 'PPK-SKPD', 'Pejabat Pembuat Komitmen', 'Pejabat Pengadaan',
        'PPTK', 'Bendahara Pengeluaran', 'Pengurus Barang',
    ];

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function scopeAktif($query)
    {
        return $query->where(function($q) {
            $q->whereNull('aktif_sampai')
              ->orWhere('aktif_sampai', '>=', now()->toDateString());
        });
    }
}