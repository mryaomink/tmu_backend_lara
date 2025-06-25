<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jadwal_pelayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rute_id')->constrained('rutes')->onDelete('cascade');
            $table->foreignId('kapal_id')->constrained('kapals')->onDelete('cascade');
            $table->timestamp('departure_time');
            $table->timestamp('arrival_time');
            $table->decimal('price_passenger', 10, 2);
            $table->json('price_vehicle_types')->comment('Harga per jenis kendaraan, cth: {"motor": 50000, "mobil": 200000}');
            $table->enum('status', ['Scheduled', 'Departed', 'Arrived', 'Cancelled'])->default('Scheduled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_pelayarans');
    }
};
