<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Direktorat
        Schema::create('direktorats', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // Contoh: Direktorat Pelayanan Medis
            $table->string('kode')->nullable(); // Opsional
            $table->timestamps();
        });

        // 2. Tabel Unit (Anak dari Direktorat)
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('direktorat_id')->constrained('direktorats')->cascadeOnDelete();
            $table->string('nama'); // Contoh: Instalasi Gawat Darurat
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
        Schema::dropIfExists('direktorats');
    }
};