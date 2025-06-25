<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'jadwal_pelayaran_id',
        'booking_code',
        'total_amount',
        'status',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    /**
     * Relasi: Sebuah Booking dimiliki oleh satu User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: Sebuah Booking dimiliki oleh satu Jadwal Pelayaran.
     */
    public function jadwalPelayaran()
    {
        return $this->belongsTo(JadwalPelayaran::class);
    }

    /**
     * Relasi: Sebuah Booking memiliki banyak Detail (manifes penumpang/kendaraan).
     */
    public function details()
    {
        return $this->hasMany(BookingDetail::class);
    }

    /**
     * Relasi: Sebuah Booking memiliki satu Payment.
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Relasi: Sebuah Booking bisa memiliki satu Refund.
     */
    public function refund()
    {
        return $this->hasOne(Refund::class);
    }
}
