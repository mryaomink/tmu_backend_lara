<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Jalankan seeder database.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat semua peran yang dibutuhkan
        $superAdminRole = Role::create(['name' => 'super admin']);
        $adminRole = Role::create(['name' => 'admin']);
        $petugasRole = Role::create(['name' => 'petugas']);
        $agenRole = Role::create(['name' => 'agen']);
        Role::create(['name' => 'pelanggan']); // Tidak perlu variabel karena tidak ada izin khusus

        // Buat beberapa izin (opsional tapi praktik yang baik)
        $manageUsersPermission = Permission::create(['name' => 'manage users']);
        $manageSchedulesPermission = Permission::create(['name' => 'manage schedules']);
        $scanTicketPermission = Permission::create(['name' => 'scan ticket']);
        $viewManifestPermission = Permission::create(['name' => 'view manifest']);

        // Berikan izin ke peran
        $superAdminRole->givePermissionTo($manageUsersPermission);
        $superAdminRole->givePermissionTo($manageSchedulesPermission);
        $superAdminRole->givePermissionTo($viewManifestPermission);

        $adminRole->givePermissionTo($manageSchedulesPermission);
        $adminRole->givePermissionTo($viewManifestPermission);

        $petugasRole->givePermissionTo($scanTicketPermission);
        $petugasRole->givePermissionTo($viewManifestPermission);
        
        // Buat pengguna Super Admin default
        $superAdminUser = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin', // Username untuk login
            'email' => 'superadmin@ferry.com',
            'password' => Hash::make('password'), // Ganti dengan password yang aman
        ]);
        
        // Berikan peran 'super admin' ke pengguna tersebut
        $superAdminUser->assignRole($superAdminRole);

        // Buat pengguna Admin default (opsional)
        $adminUser = User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@ferry.com',
            'password' => Hash::make('password'),
        ]);
        $adminUser->assignRole($adminRole);

        // Buat pengguna Petugas default (opsional)
        $petugasUser = User::create([
            'name' => 'Petugas Lapangan',
            'username' => 'petugas01',
            'email' => 'petugas01@ferry.com',
            'password' => Hash::make('password'),
        ]);
        $petugasUser->assignRole($petugasRole);
    }
}
