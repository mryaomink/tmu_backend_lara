<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Pelabuhan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class PelabuhanController extends Controller
{
    /**
     * Menampilkan daftar semua pelabuhan.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Pelabuhan::orderBy('name')->get();
    }

    /**
     * Menyimpan pelabuhan baru ke database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'kota' => 'required|string|max:255',
            'kode' => 'required|string|max:10|unique:pelabuhans,code',
        ]);

        $pelabuhan = Pelabuhan::create($validatedData);

        return response()->json($pelabuhan, Response::HTTP_CREATED);
    }

    /**
     * Menampilkan detail satu pelabuhan.
     *
     * @param  \App\Models\Pelabuhan  $pelabuhan
     * @return \Illuminate\Http\Response
     */
    public function show(Pelabuhan $pelabuhan)
    {
        return response()->json($pelabuhan);
    }

    /**
     * Memperbarui data pelabuhan yang ada.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Pelabuhan  $pelabuhan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Pelabuhan $pelabuhan)
    {
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'kota' => 'required|string|max:255',
            'kode' => [
                'required',
                'string',
                'max:10',
                Rule::unique('pelabuhans')->ignore($pelabuhan->id),
            ],
        ]);

        $pelabuhan->update($validatedData);

        return response()->json($pelabuhan, Response::HTTP_OK);
    }

    /**
     * Menghapus pelabuhan dari database.
     *
     * @param  \App\Models\Pelabuhan  $pelabuhan
     * @return \Illuminate\Http\Response
     */
    public function destroy(Pelabuhan $pelabuhan)
    {
        $pelabuhan->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
