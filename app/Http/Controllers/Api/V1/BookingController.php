<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\JadwalPelayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;

class BookingController extends Controller
{
    public function __construct()
    {
        // Konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }
    
    /**
     * Membuat booking baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jadwal_pelayaran_id' => 'required|exists:jadwal_pelayarans,id',
            'passengers' => 'required|array|min:1',
            'passengers.*.name' => 'required|string',
            'passengers.*.id_number' => 'required|string',
            'vehicles' => 'nullable|array',
            'vehicles.*.plate_number' => 'required_with:vehicles|string',
            'vehicles.*.type' => 'required_with:vehicles|string', // cth: motor, mobil
        ]);

        $jadwal = JadwalPelayaran::findOrFail($validated['jadwal_pelayaran_id']);

        // --- Logika validasi kapasitas bisa ditambahkan di sini ---

        $totalAmount = 0;
        // Hitung total harga penumpang
        $totalAmount += count($validated['passengers']) * $jadwal->price_passenger;
        // Hitung total harga kendaraan
        if (isset($validated['vehicles'])) {
            foreach ($validated['vehicles'] as $vehicle) {
                $totalAmount += $jadwal->price_vehicle_types[$vehicle['type']] ?? 0;
            }
        }
        
        $booking = null;
        DB::transaction(function () use ($validated, $jadwal, $totalAmount, &$booking) {
            // 1. Buat data booking utama
            $booking = Booking::create([
                'user_id' => auth()->id(),
                'jadwal_pelayaran_id' => $jadwal->id,
                'booking_code' => 'BOOK-' . strtoupper(Str::random(8)),
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            // 2. Buat detail penumpang
            foreach ($validated['passengers'] as $passenger) {
                $booking->details()->create([
                    'type' => 'passenger',
                    'passenger_name' => $passenger['name'],
                    'passenger_id_number' => $passenger['id_number'],
                ]);
            }

            // 3. Buat detail kendaraan
            if (isset($validated['vehicles'])) {
                foreach ($validated['vehicles'] as $vehicle) {
                    $booking->details()->create([
                        'type' => 'vehicle',
                        'vehicle_details' => $vehicle,
                    ]);
                }
            }
        });

        // 4. Generate token Midtrans Snap
        $midtransPayload = [
            'transaction_details' => [
                'order_id' => $booking->booking_code,
                'gross_amount' => $booking->total_amount,
            ],
            'customer_details' => [
                'first_name' => auth()->user()->name,
                'email' => auth()->user()->email,
            ],
        ];

        $snapToken = Snap::getSnapToken($midtransPayload);
        
        return response()->json([
            'message' => 'Booking berhasil dibuat, silakan lanjutkan pembayaran.',
            'booking' => $booking->load('details'),
            'snap_token' => $snapToken,
        ], 201);
    }

    // Fungsi index (untuk melihat riwayat) dan show (melihat detail)
    public function index()
    {
        $bookings = Booking::where('user_id', auth()->id())
            ->with('jadwalPelayaran.rute.pelabuhanAsal', 'jadwalPelayaran.rute.pelabuhanTujuan')
            ->latest()
            ->get();
        return response()->json($bookings);
    }

    public function show(Booking $booking)
    {
        // Pastikan user hanya bisa melihat booking miliknya sendiri
        $this->authorize('view', $booking); 

        return $booking->load('details', 'payment', 'jadwalPelayaran');
    }
}
