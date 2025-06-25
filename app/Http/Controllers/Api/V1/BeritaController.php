<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BeritaController extends Controller
{
    /**
     * Menampilkan daftar semua berita.
     */
    public function index()
    {
        return Berita::with('author:id,name')->latest()->get();
    }

    /**
     * Menyimpan berita baru ke database.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        // Tambahkan author_id dari user yang sedang login secara otomatis
        $berita = Berita::create(array_merge($validatedData, [
            'author_id' => auth()->id()
        ]));

        return response()->json($berita->load('author:id,name'), Response::HTTP_CREATED);
    }

    /**
     * Menampilkan detail satu berita.
     */
    public function show(Berita $beritum) // Laravel secara default akan menggunakan 'beritum'
    {
        return $beritum->load('author:id,name');
    }

    /**
     * Memperbarui data berita yang ada.
     */
    public function update(Request $request, Berita $beritum)
    {
        // Disarankan menggunakan Policy untuk memastikan hanya author atau admin yang bisa edit
        // $this->authorize('update', $beritum);

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $beritum->update($validatedData);

        return response()->json($beritum->load('author:id,name'), Response::HTTP_OK);
    }

    /**
     * Menghapus berita dari database.
     */
    public function destroy(Berita $beritum)
    {
        // Disarankan menggunakan Policy untuk memastikan hanya author atau admin yang bisa hapus
        // $this->authorize('delete', $beritum);
        
        $beritum->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
