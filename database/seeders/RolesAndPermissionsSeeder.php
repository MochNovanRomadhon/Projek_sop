<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use BezhanSalleh\FilamentShield\Support\Utils;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset permission cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /**
         * ✅ Daftar Role SOP RS
         */
        $roles = [
            'super_admin',
            'admin',
            'verifikator',
            'pengusul',
            'pegawai',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }

        /**
         * ✅ Generate otomatis permission Filament
         */
        $permissions = Utils::getPermissionModels();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        /**
         * ✅ Hak Akses
         */
        $superAdmin = Role::where('name', 'super_admin')->first();
        $admin      = Role::where('name', 'admin')->first();
        $verifikator= Role::where('name', 'verifikator')->first();
        $pengusul   = Role::where('name', 'pengusul')->first();

        // Super Admin → semua akses
        $superAdmin->syncPermissions(Permission::all());

        // Admin → semua kecuali delete user
        $admin->syncPermissions(
            Permission::whereNotIn('name', [
                'delete_user',
            ])->get()
        );

        // Verifikator → view + update SOP
        $verifikator->syncPermissions(
            Permission::whereIn('name', [
                'view_sop',
                'update_sop',
                'approve_sop',
            ])->get()
        );

        // Pengusul → create & view SOP
        $pengusul->syncPermissions(
            Permission::whereIn('name', [
                'create_sop',
                'view_sop',
            ])->get()
        );
    }
}
