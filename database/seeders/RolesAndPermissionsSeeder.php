<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Reset permission cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Daftar 4 Role Sesuai PDF SOPan
        $roles = [
            'admin',       // Mengelola User & Master Data
            'verifikator', // Humas (Approve/Reject)
            'pengusul',    // Kepala Unit (Upload)
            'pegawai'      // Staff (View Only)
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // 3. GENERATE PERMISSION SECARA OTOMATIS
        $entities = ['user', 'unit', 'direktorat', 'sop'];
        $actions = [
            'view_any',    // Lihat daftar
            'view',        // Lihat detail
            'create',      // Buat
            'update',      // Edit
            'delete',      // Hapus
            'restore',     // Restore (Soft Delete)
            'force_delete' // Hapus Permanen
        ];

        // Loop permission CRUD standar
        foreach ($entities as $entity) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$action}_{$entity}", 
                    'guard_name' => 'web'
                ]);
            }
        }

        // 4. Tambahkan Permission KHUSUS (Custom Workflow)
        $customPermissions = [
            'approve_sop',
            'reject_sop'
        ];

        foreach ($customPermissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // --- 5. ASSIGNMENT PERMISSION KE ROLE ---

        // A. ADMIN (Sekarang jadi Role Tertinggi)
        // Admin mengelola User, Unit, Direktorat, dan Bisa Melihat SOP
        Role::where('name', 'admin')->first()->givePermissionTo([
            // User Management (Full)
            'view_any_user', 'view_user', 'create_user', 'update_user', 'delete_user',
            // Unit Management (Full)
            'view_any_unit', 'view_unit', 'create_unit', 'update_unit', 'delete_unit',
            // Direktorat Management (Full)
            'view_any_direktorat', 'view_direktorat', 'create_direktorat', 'update_direktorat', 'delete_direktorat',
            // SOP (Hanya Lihat, Admin tidak ikut workflow approval)
            'view_any_sop', 'view_sop'
        ]);

        // B. VERIFIKATOR (Humas - Approve/Reject)
        Role::where('name', 'verifikator')->first()->givePermissionTo([
            // SOP Workflow
            'view_any_sop', 'view_sop', 
            'update_sop', // Perlu update untuk mengubah status
            'approve_sop', 'reject_sop', // Tombol khusus
            // Master Data (Read Only - untuk referensi)
            'view_any_unit', 'view_unit',
            'view_any_direktorat', 'view_direktorat'
        ]);

        // C. PENGUSUL (Kepala Unit - Upload)
        Role::where('name', 'pengusul')->first()->givePermissionTo([
            // SOP Management
            'view_any_sop', 'view_sop', 'create_sop', 
            'update_sop', 'delete_sop', // Dibatasi policy hanya untuk draft sendiri
            // Master Data (Read Only - untuk dropdown form)
            'view_any_unit', 'view_unit'
        ]);

        // D. PEGAWAI (View Only)
        Role::where('name', 'pegawai')->first()->givePermissionTo([
            'view_any_sop', 'view_sop'
        ]);
    }
}