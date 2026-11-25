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
    protected $guarded = [];
    protected $casts = ['status' => SpjStatus::class, 'tanggal' => 'date', 'total' => 'decimal:2'];

    public const JENIS = ['UP' => 'Uang persediaan (UP)', 'GU' => 'Ganti uang (GU)', 'TU' => 'Tambah uang (TU)', 'LS' => 'Langsung (LS)'];

    /**
     * Kategori belanja. Menentukan dokumen yang dicetak, pejabat yang wajib dipilih, dan apakah penyedia wajib.
     * Urutan 'dokumen' adalah urutan cetak.
     */
    public const KATEGORI = [
        'barang_jasa' => [
            'label' => 'Belanja barang dan jasa',
            'dokumen' => ['nota-pesanan', 'ba-pengadaan', 'ba-pemeriksaan', 'ba-penerimaan', 'bast', 'kwitansi'],
            'pejabat' => ['pptk_id', 'bendahara_id', 'pembuat_komitmen_id', 'pejabat_pengadaan_id', 'pengurus_barang_id', 'pengguna_anggaran_id'],
            'penyedia' => true,
        ],
        'honor_lembur' => [
            'label' => 'Honorarium dan lembur',
            'dokumen' => ['kwitansi', 'daftar-penerima'],
            'pejabat' => ['pptk_id', 'bendahara_id', 'pengguna_anggaran_id'],
            'penyedia' => false,
        ],
        'modal' => [
            'label' => 'Belanja modal',
            'dokumen' => ['kwitansi', 'bast'],
            'pejabat' => ['pptk_id', 'bendahara_id', 'pembuat_komitmen_id', 'pengguna_anggaran_id'],
            'penyedia' => true,
        ],
        'gaji' => [
            'label' => 'Gaji',
            'dokumen' => ['kwitansi'],
            'pejabat' => ['pptk_id', 'bendahara_id', 'pengguna_anggaran_id'],
            'penyedia' => false,
        ],
    ];

    /** Kolom pejabat => [label, jabatan di pejabat_opd yang boleh dipilih]. Semuanya wajib sebelum SPJ diajukan. */
    public const PEJABAT = [
        'pptk_id' => ['PPTK', ['PPTK']],
        'bendahara_id' => ['Bendahara pengeluaran', ['Bendahara Pengeluaran']],
        'pembuat_komitmen_id' => ['Pejabat pembuat komitmen', ['Pejabat Pembuat Komitmen']],
        'pejabat_pengadaan_id' => ['Pejabat pengadaan', ['Pejabat Pengadaan']],
        'pengurus_barang_id' => ['Pengurus barang', ['Pengurus Barang']],
        'pengguna_anggaran_id' => ['Pengguna anggaran', ['PA', 'KPA']],
    ];

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

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

    /** Pengaturan kategori SPJ ini (barang_jasa bila belum diisi). */
    public function kategoriInfo(): array
    {
        return self::KATEGORI[$this->kategori ?? 'barang_jasa'] ?? self::KATEGORI['barang_jasa'];
    }
}