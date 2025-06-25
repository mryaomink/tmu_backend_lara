<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelabuhan extends Model
{
    use HasFactory;

    protected $fillable = ['nama', 'kota', 'kode'];

    /**
     * Relasi: Sebuah pelabuhan bisa menjadi asal dari banyak rute.
     */
    public function ruteAsal()
    {
        return $this->hasMany(Rute::class, 'origin_port_id');
    }

    /**
     * Relasi: Sebuah pelabuhan bisa menjadi tujuan dari banyak rute.
     */
    public function ruteTujuan()
    {
        return $this->hasMany(Rute::class, 'destination_port_id');
    }
}
