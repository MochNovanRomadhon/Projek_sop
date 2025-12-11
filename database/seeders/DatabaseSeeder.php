<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Unit;
use App\Models\Direktorat;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Panggil Seeder Role & Permission dulu
        // Pastikan file RolesAndPermissionsSeeder sudah ada dan membuat role: admin, verifikator, pengusul, pegawai
        $this->call(RolesAndPermissionsSeeder::class);

        // 2. Buat Data Master Dummy
        $dirMedis = Direktorat::create(['nama' => 'Direktorat Pelayanan Medis']);
        $dirUmum = Direktorat::create(['nama' => 'Direktorat Umum & Operasional']);

        $unitIGD = Unit::create(['nama' => 'Instalasi Gawat Darurat', 'direktorat_id' => $dirMedis->id]);
        $unitHumas = Unit::create(['nama' => 'Humas & Protokoler', 'direktorat_id' => $dirUmum->id]);

        // 3. Buat User untuk Testing
        // PERBAIKAN: Hapus bcrypt(), gunakan string biasa 'password' karena Model User sudah auto-hash.

        // A. Admin (Login di /admin/login)
        $admin = User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@sopan.com',
            'password' => 'password', // <--- JANGAN PAKAI BCRYPT
            'is_active' => true,
            'unit_id' => null,
        ]);
        $admin->assignRole('admin');

        // B. Verifikator (Login di /verifikator/login)
        $verifikator = User::factory()->create([
            'name' => 'Staff Humas',
            'email' => 'verifikator@sopan.com',
            'password' => 'password', // <--- JANGAN PAKAI BCRYPT
            'unit_id' => $unitHumas->id,
            'is_active' => true,
        ]);
        $verifikator->assignRole('verifikator');

        // C. Pengusul (Login di /pengusul/login)
        $pengusul = User::factory()->create([
            'name' => 'Kepala IGD',
            'email' => 'pengusul@sopan.com',
            'password' => 'password', // <--- JANGAN PAKAI BCRYPT
            'unit_id' => $unitIGD->id,
            'is_active' => true,
        ]);
        $pengusul->assignRole('pengusul');

        // D. Pegawai (Login di /pegawai/login)
        $pegawai = User::factory()->create([
            'name' => 'Staff Biasa',
            'email' => 'pegawai@sopan.com',
            'password' => 'password', // <--- JANGAN PAKAI BCRYPT
            'unit_id' => $unitIGD->id,
            'is_active' => true,
        ]);
        $pegawai->assignRole('pegawai');
    }
}