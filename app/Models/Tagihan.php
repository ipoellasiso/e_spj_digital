<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    use HasFactory;

    // Tambahkan fillable
    protected $fillable = [
        'opd_id',
        'paket_id',
        'no_invoice',
        'total_bayar',
        'periode_mulai',
        'periode_berakhir',
        'status',
        'bukti_transfer',
    ];

    // Casting tanggal agar otomatis menjadi objek Carbon
    protected $casts = [
        'periode_mulai' => 'date',
        'periode_berakhir' => 'date',
    ];

    // Relasi: Tagihan milik satu OPD
    public function opd()
    {
        // Ingat, karena tabel OPD kamu bernama 'opd' (tanpa s), pastikan Model Opd sudah di-set $table = 'opd'
        return $this->belongsTo(Opd::class, 'opd_id');
    }

    // Relasi: Tagihan milik satu Paket
    public function paket()
    {
        return $this->belongsTo(Paket::class, 'paket_id');
    }
}