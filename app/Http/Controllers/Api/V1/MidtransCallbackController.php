<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Notification;

class MidtransCallbackController extends Controller
{
    /**
     * Menangani notifikasi dari Midtrans.
     */
    public function handle(Request $request)
    {
        // Set konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');

        try {
            $notification = new Notification();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid notification payload.'], 400);
        }

        $transactionStatus = $notification->transaction_status;
        $fraudStatus = $notification->fraud_status;
        $orderId = $notification->order_id; // Ini adalah booking_code kita

        $booking = Booking::where('booking_code', $orderId)->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        // --- Logika validasi signature key Midtrans bisa ditambahkan di sini untuk keamanan ekstra ---

        if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
            if ($fraudStatus == 'accept') {
                // Update status booking & payment
                $booking->status = 'confirmed';
                $booking->payment()->updateOrCreate(
                    ['booking_id' => $booking->id],
                    [
                        'status' => 'success',
                        'paid_at' => now(),
                        'payment_method' => $notification->payment_type,
                        'midtrans_transaction_id' => $notification->transaction_id,
                    ]
                );

                // Generate Tiket untuk setiap detail booking
                foreach ($booking->details as $detail) {
                    Ticket::updateOrCreate(
                        ['booking_detail_id' => $detail->id],
                        [
                            'ticket_code' => 'TICKET-' . strtoupper(Str::random(10)),
                            'qr_code_url' => 'generate-qr-code-url-here', // Logika generate QR
                        ]
                    );
                }
            }
        } else if ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            $booking->status = 'cancelled';
            $booking->payment()->updateOrCreate(
                ['booking_id' => $booking->id],
                ['status' => 'failed']
            );
        }

        $booking->save();

        return response()->json(['message' => 'Notification handled.']);
    }
}
