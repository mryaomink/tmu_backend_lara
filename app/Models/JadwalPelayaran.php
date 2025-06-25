<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPelayaran extends Model
{
    use HasFactory;

    protected $table = 'jadwal_pelayarans';

    protected $fillable = [
        'rute_id',
        'kapal_id',
        'departure_time',
        'arrival_time',
        'price_passenger',
        'price_vehicle_types',
        'status',
    ];

    protected $casts = [
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
        'price_passenger' => 'decimal:2',
        'price_vehicle_types' => 'json',
    ];

    /**
     * Relasi: Sebuah Jadwal Pelayaran dimiliki oleh satu Rute.
     */
    public function rute()
    {
        return $this->belongsTo(Rute::class);
    }

    /**
     * Relasi: Sebuah Jadwal Pelayaran dimiliki oleh satu Kapal.
     */
    public function kapal()
    {
        return $this->belongsTo(Kapal::class);
    }

    /**
     * Relasi: Sebuah Jadwal Pelayaran bisa memiliki banyak Booking.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
