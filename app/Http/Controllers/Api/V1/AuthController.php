<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Registrasi user baru, sekarang dengan data profil.
     */
    public function register(Request $request)
    {
        // --- LOGIKA DIPERBARUI SESUAI PANDUAN ---
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone_number' => 'nullable|string', // Field tambahan untuk profil
            'address' => 'nullable|string',      // Field tambahan untuk profil
            'foto_url' => 'nullable|string',      // Field tambahan untuk profil
        ]);

        // Gunakan DB Transaction untuk memastikan kedua data tersimpan atau tidak sama sekali
        DB::transaction(function () use ($validatedData) {
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
            ]);

            $user->assignRole('pelanggan');

            // Buat profil pelanggan terkait
            $user->customerProfile()->create([
                'phone_number' => $validatedData['phone_number'] ?? null,
                'address' => $validatedData['address'] ?? null,
                'foto_url' => $validatedData['foto_url'] ?? null,
            ]);
        });
        // --- AKHIR PERUBAHAN ---

        return response()->json(['message' => 'Registrasi berhasil.'], 201);
    }

    /**
     * Login untuk semua jenis user, sekarang memuat data profil.
     */
   // app/Http/Controllers/Api/V1/AuthController.php

public function login(Request $request)
{
    $request->validate([
        'identifier' => 'required|string',
        'password' => 'required|string',
    ]);

    $identifier = $request->identifier;

    // Cek apakah identifier adalah email atau username
    $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

    // Coba otentikasi
    $credentials = [
        $field => $identifier,
        'password' => $request->password
    ];

    if (!Auth::attempt($credentials)) {
        throw ValidationException::withMessages([
            'identifier' => ['Kredensial yang diberikan tidak cocok dengan data kami.'],
        ]);
    }

    $user = Auth::user();

    // Hapus token lama jika ada & buat token baru
    $user->tokens()->delete();
    $token = $user->createToken('auth-token')->plainTextToken;

    // Muat relasi peran
    $user->load('roles');
    $role = $user->getRoleNames()->first();

    return response()->json([
        'accessToken' => $token,
        'token_type' => 'Bearer',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'role' => $role,
            // =======================================================
            // ==> TAMBAHKAN SATU BARIS INI UNTUK MEMPERBAIKINYA <==
            'permissions' => $user->getAllPermissions()->pluck('name'),
            // =======================================================
        ]
    ]);
}

    /**
     * Menangani callback login dari Google.
     */
    public function handleGoogleCallback(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        try {
            $socialUser = Socialite::driver('google')->userFromToken($request->token);

            $user = null;
            DB::transaction(function() use ($socialUser, &$user) {
                $user = User::updateOrCreate(
                    ['google_id' => $socialUser->getId()],
                    [
                        'name' => $socialUser->getName(),
                        'email' => $socialUser->getEmail(),
                        'password' => null, // Login sosial media tidak punya password
                    ]
                );
    
                if ($user->wasRecentlyCreated) {
                    $user->assignRole('pelanggan');
                    // Buat profil pelanggan kosong saat mendaftar via Google
                    $user->customerProfile()->create();
                }
            });


            Auth::login($user);
            
            // Muat data profil setelah login
            $user->load('roles:name', 'customerProfile');
            
            $token = $user->createToken('auth-token-google-' . $user->id)->plainTextToken;
            
            return response()->json([
                'accessToken' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Otentikasi Google gagal.', 'message' => $e->getMessage()], 401);
        }
    }

    /**
     * Mendapatkan data user yang sedang login.
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('roles:name', 'permissions:name', 'customerProfile', 'agentProfile'); // Muat semua profil yang mungkin

        return response()->json($user);
    }

    /**
     * Logout user.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil.']);
    }
}
