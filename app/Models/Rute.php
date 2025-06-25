<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rute extends Model
{
    use HasFactory;

    protected $fillable = ['origin_port_id', 'destination_port_id'];

    /**
     * Relasi: Sebuah Rute dimiliki oleh satu Pelabuhan asal.
     */
    public function pelabuhanAsal()
    {
        return $this->belongsTo(Pelabuhan::class, 'origin_port_id');
    }

    /**
     * Relasi: Sebuah Rute dimiliki oleh satu Pelabuhan tujuan.
     */
    public function pelabuhanTujuan()
    {
        return $this->belongsTo(Pelabuhan::class, 'destination_port_id');
    }

    /**
     * Relasi: Sebuah Rute bisa memiliki banyak Jadwal Pelayaran.
     */
    public function jadwalPelayarans()
    {
        return $this->hasMany(JadwalPelayaran::class);
    }
}
