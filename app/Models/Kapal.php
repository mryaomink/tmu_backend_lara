<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kapal extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kapasitas_penumpang',
        'kapasitas_kendaraan',
    ];

    protected $casts = [
        'kapasitas_kendaraan_details' => 'json', // Otomatis konversi ke/dari JSON
    ];

    /**
     * Relasi: Sebuah Kapal bisa memiliki banyak Jadwal Pelayaran.
     */
    public function jadwalPelayarans()
    {
        return $this->hasMany(JadwalPelayaran::class);
    }
}
