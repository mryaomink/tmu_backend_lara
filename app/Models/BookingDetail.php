<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'type',
        'passenger_name',
        'passenger_id_number',
        'vehicle_details',
    ];

    protected $casts = [
        'vehicle_details' => 'json',
    ];

    /**
     * Relasi: Sebuah Detail Booking dimiliki oleh satu Booking.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Relasi: Setiap detail booking (penumpang/kendaraan) memiliki satu Tiket.
     */
    public function ticket()
    {
        return $this->hasOne(Ticket::class);
    }
}
