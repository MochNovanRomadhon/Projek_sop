<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sops', function (Blueprint $table) {
            // Kita ubah tipe kolom menjadi string biasa dengan panjang 255
            // Agar bisa menampung 'Review', 'Riwayat', dll tanpa batasan ENUM
            $table->string('status', 255)->change();
        });
    }

    public function down(): void
    {
        Schema::table('sops', function (Blueprint $table) {
            // Kembalikan ke enum jika rollback (sesuaikan dengan enum lama Anda)
            // Ini opsional, bisa dibiarkan string juga tidak masalah
            // $table->enum('status', ['Draft', 'Menunggu Verifikasi', 'Disetujui', 'Ditolak'])->change();
        });
    }
};