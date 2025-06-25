<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelayaran;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class JadwalPelayaranController extends Controller
{
    /**
     * Menampilkan daftar jadwal pelayaran dengan filter.
     * Endpoint ini bisa diakses publik untuk mencari jadwal.
     * Contoh query: /api/v1/jadwal-pelayaran?origin_port_id=1&destination_port_id=2&departure_date=2025-12-31
     */
    public function index(Request $request)
    {
        $request->validate([
            'origin_port_id' => 'sometimes|required|integer|exists:pelabuhans,id',
            'destination_port_id' => 'sometimes|required|integer|exists:pelabuhans,id',
            'departure_date' => 'sometimes|required|date',
        ]);

        $query = JadwalPelayaran::query()->with([
            'rute.pelabuhanAsal:id,name,city,code', 
            'rute.pelabuhanTujuan:id,name,city,code', 
            'kapal:id,name,capacity_passengers,capacity_vehicles'
        ]);

        // Filter berdasarkan rute jika parameter tersedia
        if ($request->filled('origin_port_id') && $request->filled('destination_port_id')) {
            $query->whereHas('rute', function ($q) use ($request) {
                $q->where('origin_port_id', $request->origin_port_id)
                  ->where('destination_port_id', $request->destination_port_id);
            });
        }
        
        // Filter berdasarkan tanggal keberangkatan jika parameter tersedia
        if ($request->filled('departure_date')) {
            $query->whereDate('departure_time', $request->departure_date);
        }

        // Hanya tampilkan jadwal yang akan datang dan urutkan dari yang terdekat
        return $query->where('departure_time', '>=', now())
                     ->orderBy('departure_time', 'asc')
                     ->get();
    }

    /**
     * Menyimpan jadwal pelayaran baru.
     * Hanya bisa diakses oleh Admin atau Super Admin.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'rute_id' => 'required|exists:rutes,id',
            'kapal_id' => 'required|exists:kapals,id',
            'departure_time' => 'required|date|after:now',
            'arrival_time' => 'required|date|after:departure_time',
            'price_passenger' => 'required|numeric|min:0',
            'price_vehicle_types' => 'required|json',
            'status' => 'sometimes|in:Scheduled,Departed,Arrived,Cancelled',
        ]);

        $jadwal = JadwalPelayaran::create($validatedData);

        return response()->json($jadwal->load(['rute', 'kapal']), Response::HTTP_CREATED);
    }

    /**
     * Menampilkan detail satu jadwal pelayaran.
     * Bisa diakses publik.
     */
    public function show(JadwalPelayaran $jadwalPelayaran)
    {
        return $jadwalPelayaran->load([
            'rute.pelabuhanAsal:id,name,city,code', 
            'rute.pelabuhanTujuan:id,name,city,code', 
            'kapal'
        ]);
    }

    /**
     * Memperbarui data jadwal pelayaran.
     * Hanya bisa diakses oleh Admin atau Super Admin.
     */
    public function update(Request $request, JadwalPelayaran $jadwalPelayaran)
    {
        $validatedData = $request->validate([
            'rute_id' => 'sometimes|required|exists:rutes,id',
            'kapal_id' => 'sometimes|required|exists:kapals,id',
            'departure_time' => 'sometimes|required|date',
            'arrival_time' => 'sometimes|required|date|after:departure_time',
            'price_passenger' => 'sometimes|required|numeric|min:0',
            'price_vehicle_types' => 'sometimes|required|json',
            'status' => 'sometimes|required|in:Scheduled,Departed,Arrived,Cancelled',
        ]);

        $jadwalPelayaran->update($validatedData);

        return response()->json($jadwalPelayaran->load(['rute', 'kapal']), Response::HTTP_OK);
    }

    /**
     * Menghapus jadwal pelayaran.
     * Hanya bisa diakses oleh Admin atau Super Admin.
     */
    public function destroy(JadwalPelayaran $jadwalPelayaran)
    {
        $jadwalPelayaran->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
