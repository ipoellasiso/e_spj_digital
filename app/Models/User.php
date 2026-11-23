<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
// use HasRoles;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;   // tambahkan HasRoles ke daftar yang sudah ada

    // tambahkan opd_id, nip, aktif ke $fillable yang sudah ada
    protected $fillable = ['name', 'email', 'password', 'opd_id', 'nip', 'aktif'];

    public function opd() { return $this->belongsTo(Opd::class); }

    public function isSuperadmin(): bool { return $this->hasRole('superadmin'); }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

}
