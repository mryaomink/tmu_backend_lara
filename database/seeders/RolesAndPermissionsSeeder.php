<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus cache permission
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Daftar permission yang akan dibuat
        $permissions = [
            'manage users',
            'manage schedules',
            'manage ships',
            'manage routes',
            'manage ports',
            'manage news',
            'manage manifest',
            'manage refund',
            'scan ticket',
        ];

        // Buat permission jika belum ada
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Buat role-role utama
        $superAdminRole = Role::firstOrCreate(['name' => 'super admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $petugasRole = Role::firstOrCreate(['name' => 'petugas', 'guard_name' => 'web']);
        $agenRole = Role::firstOrCreate(['name' => 'agen', 'guard_name' => 'web']);
        $pelangganRole = Role::firstOrCreate(['name' => 'pelanggan', 'guard_name' => 'web']);

        // Sinkronisasi permission untuk setiap role
        $superAdminRole->syncPermissions(Permission::all());

        $adminRole->syncPermissions([
            'manage schedules',
            'manage ships',
            'manage routes',
            'manage ports',
            'manage news',
            'manage manifest',
            'manage refund',
        ]);

        $petugasRole->syncPermissions([
            'scan ticket',
            'manage manifest',
        ]);

        $agenRole->syncPermissions([]); // belum ada permission default
        $pelangganRole->syncPermissions([]); // belum ada permission default

        // Buat user superadmin jika belum ada
        if (!User::where('username', 'superadmin')->exists()) {
            $superAdminUser = User::create([
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'email' => 'superadmin@ferry.com',
                'password' => Hash::make('password'), // Ganti dengan password aman di production
            ]);

            $superAdminUser->assignRole($superAdminRole);
        }
    }
}
