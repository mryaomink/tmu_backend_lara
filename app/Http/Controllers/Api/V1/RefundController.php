<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    /**
     * Menampilkan semua permintaan refund.
     * Hanya untuk Admin / Super Admin.
     */
    public function index()
    {
        return Refund::with('booking.user:id,name')->latest()->get();
    }

    /**
     * Memperbarui status permintaan refund.
     * Hanya untuk Admin / Super Admin.
     */
    public function updateStatus(Request $request, Refund $refund)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,processed',
        ]);

        // Logika tambahan bisa ditambahkan di sini
        // Misalnya, jika status 'approved', trigger proses pengembalian dana
        // Jika 'processed', tandai bahwa dana sudah dikirim

        $refund->update(['status' => $validated['status']]);

        // Jika statusnya approved atau rejected, update juga status booking
        if (in_array($validated['status'], ['approved', 'rejected'])) {
             $bookingStatus = $validated['status'] == 'approved' ? 'refunded' : 'confirmed'; // Kembali ke confirmed jika ditolak
             $refund->booking()->update(['status' => $bookingStatus]);
        }

        return response()->json($refund->load('booking'));
    }
}
