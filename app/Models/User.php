<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles; // <-- Import trait HasRoles

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles; // <-- Gunakan trait HasRoles

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username', // Untuk login staf
        'google_id', // Untuk login sosial media
    ];

    /**
     * Atribut yang harus disembunyikan saat serialisasi.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Atribut yang harus di-cast ke tipe data tertentu.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Otomatis hash saat di-set
    ];

    /**
     * Relasi: Seorang User bisa memiliki banyak Booking.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Relasi: Seorang User (author) bisa menulis banyak Berita.
     */
    public function beritas()
    {
        return $this->hasMany(Berita::class, 'author_id');
    }

    public function customerProfile()
    {
        return $this->hasOne(CustomerProfile::class);
    }

/**
 * Relasi: Seorang User mungkin memiliki satu profil agen.
 */
    public function agentProfile()
    {
        return $this->hasOne(AgentProfile::class);
    }
}
