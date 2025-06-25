<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_detail_id',
        'ticket_code',
        'qr_code_url',
        'is_scanned',
        'scanned_at',
    ];

    protected $casts = [
        'is_scanned' => 'boolean',
        'scanned_at' => 'datetime',
    ];

    /**
     * Relasi: Sebuah Tiket dimiliki oleh satu BookingDetail.
     */
    public function bookingDetail()
    {
        return $this->belongsTo(BookingDetail::class);
    }
}
