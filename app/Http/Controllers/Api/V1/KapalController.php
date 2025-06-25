<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Kapal;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class KapalController extends Controller
{
    /**
     * Menampilkan daftar semua kapal.
     */
    public function index()
    {
        return Kapal::all();
    }

    /**
     * Menyimpan kapal baru ke database.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255|unique:kapals',
            'kapasitas_penumpang' => 'required|integer|min:1',
            'kapasitas_kendaraan' => 'required|integer|min:0',
            'kapasitas_kendaraan_details' => 'nullable|json',
        ]);

        $kapal = Kapal::create($validatedData);
        return response()->json($kapal, Response::HTTP_CREATED);
    }

    /**
     * Menampilkan detail satu kapal.
     */
    public function show(Kapal $kapal)
    {
        return $kapal;
    }

    /**
     * Memperbarui data kapal yang ada.
     */
    public function update(Request $request, Kapal $kapal)
    {
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255|unique:kapals,nama' . $kapal->id,
            'kapasitas_penumpang' => 'required|integer|min:1',
            'kapasitas_kendaraan' => 'required|integer|min:0',
            'kapasitas_kendaraan_details' => 'nullable|json',
        ]);

        $kapal->update($validatedData);
        return response()->json($kapal, Response::HTTP_OK);
    }

    /**
     * Menghapus kapal dari database.
     */
    public function destroy(Kapal $kapal)
    {
        $kapal->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
