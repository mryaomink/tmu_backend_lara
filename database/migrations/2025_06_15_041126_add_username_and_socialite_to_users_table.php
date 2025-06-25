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
        Schema::table('users', function (Blueprint $table) {
            // Tambahkan kolom username, harus unik tapi boleh null
            $table->string('username')->unique()->nullable()->after('name');

            // Jadikan kolom email bisa null, karena staf login pakai username
            // dan pelanggan bisa daftar via Google tanpa email (jarang, tapi mungkin)
            $table->string('email')->nullable()->change();

            // Jadikan kolom password bisa null, karena pelanggan yang
            // daftar via Google tidak akan punya password
            $table->string('password')->nullable()->change();

            // Kolom untuk menyimpan ID unik dari Google
            $table->string('google_id')->nullable()->unique()->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'google_id']);
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });
    }
    
};
