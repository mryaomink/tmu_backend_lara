<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Menampilkan daftar semua pengguna beserta perannya.
     * Hanya bisa diakses oleh Super Admin.
     */
    public function index()
    {
        return User::with('roles:id,name')->get();
    }

    /**
     * Membuat pengguna baru (biasanya untuk staf seperti petugas atau agen).
     * Hanya bisa diakses oleh Super Admin.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'nullable|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name', // Validasi bahwa peran ada di tabel roles
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'username' => $validatedData['username'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        $user->assignRole($validatedData['role']);

        return response()->json($user->load('roles:id,name'), Response::HTTP_CREATED);
    }

    /**
     * Menampilkan detail satu pengguna.
     */
    public function show(User $user)
    {
        return $user->load('roles:id,name');
    }

    /**
     * Memperbarui data pengguna.
     */
    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'username' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'sometimes|nullable|string|min:8|confirmed',
            'role' => 'sometimes|required|string|exists:roles,name',
        ]);

        $user->update($validatedData);

        // Jika ada input password baru, hash dan simpan
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        // Jika ada input peran baru, sinkronkan perannya
        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        }

        return response()->json($user->load('roles:id,name'), Response::HTTP_OK);
    }

    /**
     * Menghapus pengguna.
     */
    public function destroy(User $user)
    {
        // Tambahkan logika untuk mencegah user menghapus dirinya sendiri
        if (auth()->id() === $user->id) {
            return response()->json(['error' => 'Anda tidak bisa menghapus akun Anda sendiri.'], 403);
        }

        $user->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
