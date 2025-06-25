<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BookingDetail;
use App\Models\JadwalPelayaran;
use Illuminate\Http\Request;

class ManifestController extends Controller
{
    /**
     * Menampilkan manifes penumpang untuk jadwal pelayaran tertentu.
     */
    public function showPassengers(JadwalPelayaran $jadwalPelayaran)
    {
        $passengers = BookingDetail::where('type', 'passenger')
            ->whereHas('booking', function ($query) use ($jadwalPelayaran) {
                $query->where('jadwal_pelayaran_id', $jadwalPelayaran->id)
                      ->where('status', 'confirmed'); // Hanya tampilkan booking yang sudah dikonfirmasi
            })
            ->with('booking:id,booking_code,user_id', 'booking.user:id,name') // Ambil data user yang booking
            ->get();

        return response()->json($passengers);
    }

    /**
     * Menampilkan manifes kendaraan untuk jadwal pelayaran tertentu.
     */
    public function showVehicles(JadwalPelayaran $jadwalPelayaran)
    {
        $vehicles = BookingDetail::where('type', 'vehicle')
            ->whereHas('booking', function ($query) use ($jadwalPelayaran) {
                $query->where('jadwal_pelayaran_id', $jadwalPelayaran->id)
                      ->where('status', 'confirmed'); // Hanya tampilkan booking yang sudah dikonfirmasi
            })
            ->with('booking:id,booking_code,user_id', 'booking.user:id,name')
            ->get();
        
        return response()->json($vehicles);
    }
}
