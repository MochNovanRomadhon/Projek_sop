<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // [PENTING] Import DB

return new class extends Migration
{
    public function up(): void
    {
        // KITA GUNAKAN RAW SQL AGAR PASTI BERHASIL
        // Ini akan mengubah tipe kolom 'status' menjadi VARCHAR(255) (Teks bebas)
        // Dan mengatur nilai defaultnya menjadi 'Draft'
        DB::statement("ALTER TABLE sops MODIFY COLUMN status VARCHAR(255) NOT NULL DEFAULT 'Draft'");
    }

    public function down(): void
    {
        // Mengembalikan ke ENUM jika di-rollback (Opsional)
        // DB::statement("ALTER TABLE sops MODIFY COLUMN status ENUM('Draft', 'Menunggu Verifikasi', 'Disetujui', 'Ditolak') NOT NULL DEFAULT 'Draft'");
    }
};