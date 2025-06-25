<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;


class TicketController extends Controller
{
    /**
     * Memvalidasi tiket berdasarkan kode tiket dari hasil scan.
     * Endpoint ini akan diakses oleh Petugas.
     */
    public function scan(Request $request)
    {
        $validated = $request->validate([
            'ticket_code' => 'required|string|exists:tickets,ticket_code',
        ]);

        $ticket = Ticket::where('ticket_code', $validated['ticket_code'])
                        ->with('bookingDetail', 'bookingDetail.booking.jadwalPelayaran')
                        ->firstOrFail();

        // 1. Cek apakah tiket sudah pernah di-scan
        if ($ticket->is_scanned) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tiket ini sudah pernah di-scan.',
                'scanned_at' => $ticket->scanned_at->toDateTimeString(),
            ], 409); // 409 Conflict
        }

        // 2. Cek apakah jadwal keberangkatan sudah lewat (opsional, tapi bagus)
        // if ($ticket->bookingDetail->booking->jadwalPelayaran->departure_time < now()) {
        //     return response()->json([
        //          'status' => 'error',
        //          'message' => 'Jadwal keberangkatan untuk tiket ini sudah lewat.'
        //      ], 400);
        // }

        // 3. Jika semua validasi lolos, update status tiket
        $ticket->update([
            'is_scanned' => true,
            'scanned_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Tiket berhasil divalidasi.',
            'ticket' => $ticket->load('bookingDetail'),
        ]);
    }
}
