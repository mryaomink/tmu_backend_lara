<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Import semua controller yang akan digunakan
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BeritaController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\JadwalPelayaranController;
use App\Http\Controllers\Api\V1\KapalController;
use App\Http\Controllers\Api\V1\ManifestController;
use App\Http\Controllers\Api\V1\MidtransCallbackController;
use App\Http\Controllers\Api\V1\PelabuhanController;
use App\Http\Controllers\Api\V1\RefundController;
use App\Http\Controllers\Api\V1\RuteController;
use App\Http\Controllers\Api\V1\TicketController;
use App\Http\Controllers\Api\V1\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Di sini Anda mendaftarkan rute API untuk aplikasi Anda. Rute-rute ini
| dimuat oleh RouteServiceProvider dan semuanya akan diberi prefix /api.
|
*/

// Rute untuk Midtrans Webhook (tanpa prefix /v1 dan tanpa otentikasi)
Route::post('/midtrans/callback', [MidtransCallbackController::class, 'handle']);

// Grup rute untuk API versi 1 dengan prefix /v1
Route::prefix('v1')->group(function () {

    //==============================================================
    // RUTE PUBLIK (Tidak Perlu Login)
    //==============================================================
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

    // Rute untuk melihat data master yang bersifat publik
    Route::get('/pelabuhan', [PelabuhanController::class, 'index']);
    Route::get('/pelabuhan/{pelabuhan}', [PelabuhanController::class, 'show']);
    Route::get('/berita', [BeritaController::class, 'index']);
    Route::get('/berita/{beritum}', [BeritaController::class, 'show']);

    // Rute utama untuk mencari jadwal pelayaran
    Route::get('/jadwal-pelayaran', [JadwalPelayaranController::class, 'index']);
    Route::get('/jadwal-pelayaran/{jadwalPelayaran}', [JadwalPelayaranController::class, 'show']);


    //==============================================================
    // RUTE YANG MEMERLUKAN OTENTIKASI (Semua Role yang Login)
    //==============================================================
    Route::middleware('auth:sanctum')->group(function () {
        // Otentikasi
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        // Booking (untuk pelanggan)
        Route::post('/booking', [BookingController::class, 'store']);
        Route::get('/booking', [BookingController::class, 'index']);
        Route::get('/booking/{booking}', [BookingController::class, 'show']);
        
        // --- RUTE KHUSUS UNTUK PERAN TERTENTU ---

        // Rute untuk Petugas
        Route::middleware('role:petugas|admin|super admin')->group(function () {
            Route::post('/ticket/scan', [TicketController::class, 'scan']);
        });

        // Rute untuk Admin & Super Admin
        Route::middleware('role:admin|super admin')->group(function () {
            // CRUD Master Data
            Route::apiResource('/kapal', KapalController::class);
            Route::apiResource('/rute', RuteController::class);
            
            // CRUD Jadwal & Berita
            Route::apiResource('/jadwal-pelayaran', JadwalPelayaranController::class)->except(['index', 'show']); // index & show sudah publik
            Route::apiResource('/berita', BeritaController::class)->except(['index', 'show']); // index & show sudah publik
            Route::apiResource('/pelabuhan', PelabuhanController::class)->except(['index', 'show']); // index & show sudah publik

            // Manajemen Refund
            Route::get('/refund', [RefundController::class, 'index']);
            Route::patch('/refund/{refund}', [RefundController::class, 'updateStatus']); // Menggunakan PATCH untuk update parsial

            // Manajemen Manifes
            Route::get('/manifest/passengers/{jadwalPelayaran}', [ManifestController::class, 'showPassengers']);
            Route::get('/manifest/vehicles/{jadwalPelayaran}', [ManifestController::class, 'showVehicles']);
        });
        
        // Rute hanya untuk Super Admin
        Route::middleware('role:super admin')->group(function () {
            Route::apiResource('/users', UserController::class);
            // Rute untuk mendapatkan semua permissions
            Route::get('/permissions', [App\Http\Controllers\Api\V1\PermissionController::class, 'index']);
            
            // Rute untuk mendapatkan semua roles
            Route::get('/roles', [App\Http\Controllers\Api\V1\RoleController::class, 'index']);
            
            // Rute untuk mendapatkan satu role beserta permission-nya
            Route::get('/roles/{role}', [App\Http\Controllers\Api\V1\RoleController::class, 'show']);

            // Rute untuk memperbarui permission milik sebuah role
            Route::put('/roles/{role}/permissions', [App\Http\Controllers\Api\V1\RoleController::class, 'updatePermissions']);
        });
    });
});
