<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Rute;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class RuteController extends Controller
{
    /**
     * Menampilkan daftar semua rute, beserta data pelabuhan terkait.
     */
    public function index()
    {
        // Gunakan `with()` untuk eager loading agar lebih efisien
        return Rute::with(['pelabuhanAsal:id,nama,kota', 'pelabuhanTujuan:id,nama,kota'])->get();
    }

    /**
     * Menyimpan rute baru ke database.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'origin_port_id' => 'required|exists:pelabuhans,id',
            'destination_port_id' => 'required|exists:pelabuhans,id|different:origin_port_id',
        ]);

        $rute = Rute::create($validatedData);

        return response()->json($rute->load(['pelabuhanAsal', 'pelabuhanTujuan']), Response::HTTP_CREATED);
    }

    /**
     * Menampilkan detail satu rute.
     */
    public function show(Rute $rute)
    {
        return $rute->load(['pelabuhanAsal', 'pelabuhanTujuan']);
    }

    /**
     * Memperbarui data rute yang ada.
     */
    public function update(Request $request, Rute $rute)
    {
        $validatedData = $request->validate([
            'origin_port_id' => 'required|exists:pelabuhans,id',
            'destination_port_id' => 'required|exists:pelabuhans,id|different:origin_port_id',
        ]);

        $rute->update($validatedData);

        return response()->json($rute->load(['pelabuhanAsal', 'pelabuhanTujuan']), Response::HTTP_OK);
    }

    /**
     * Menghapus rute dari database.
     */
    public function destroy(Rute $rute)
    {
        $rute->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
