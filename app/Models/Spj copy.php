<?php

namespace App\Models;

use App\Enums\SpjStatus;
use App\Models\Concerns\BelongsToOpd;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Spj extends Model
{
    use BelongsToOpd;

    protected $table = 'spj';
    
    // Ganti $guarded = [] dengan $fillable spesifik agar status bisa diupdate
    protected $fillable = [
        'opd_id',
        'tahun',
        'anggaran_rekening_id',
        'sumber_dana_id',
        'jenis',
        'nomor_urut',
        'tanggal',
        'uraian',
        'penyedia_id',
        'total',
        'status', // <-- Pastikan ini ada
        'dibuat_oleh',
        'pptk_id',
        'bendahara_id',
        'pembuat_komitmen_id',
        'pejabat_pengadaan_id',
        'pengurus_barang_id',
        'pengguna_anggaran_id',
        'jenis_pengeluaran',
    ];

    protected $casts = [
        'status' => SpjStatus::class, 
        'tanggal' => 'date', 
        'total' => 'decimal:2'
    ];

    public const JENIS = ['UP' => 'Uang persediaan (UP)', 'GU' => 'Ganti uang (GU)', 'TU' => 'Tambah uang (TU)', 'LS' => 'Langsung (LS)'];

    /** Kolom pejabat => [label, jabatan di pejabat_opd yang boleh dipilih]. Semuanya wajib sebelum SPJ diajukan. */
    public const PEJABAT = [
        'pptk_id' => ['PPTK', ['PPTK']],
        'bendahara_id' => ['Bendahara pengeluaran', ['Bendahara Pengeluaran']],
        'pembuat_komitmen_id' => ['Pejabat pembuat komitmen', ['Pejabat Pembuat Komitmen']],
        'pejabat_pengadaan_id' => ['Pejabat pengadaan', ['Pejabat Pengadaan']],
        'pengurus_barang_id' => ['Pengurus barang', ['Pengurus Barang']],
        'pengguna_anggaran_id' => ['Pengguna anggaran', ['PA', 'KPA']],
    ];

    public function anggaranRekening(): BelongsTo
    {
        return $this->belongsTo(AnggaranRekening::class);
    }

    public function sumberDana(): BelongsTo
    {
        return $this->belongsTo(SumberDana::class);
    }

    public function penyedia(): BelongsTo
    {
        return $this->belongsTo(Penyedia::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SpjItem::class);
    }

    public function approval(): HasMany
    {
        return $this->hasMany(SpjApproval::class)->orderBy('id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function pejabat(string $kolom): BelongsTo
    {
        return $this->belongsTo(PejabatOpd::class, $kolom);
    }

    /** Nomor urut 4 digit (mis. 0263), null bila belum pernah diajukan. */
    public function getNomorAttribute(): ?string
    {
        return $this->nomor_urut ? str_pad((string) $this->nomor_urut, 4, '0', STR_PAD_LEFT) : null;
    }
}