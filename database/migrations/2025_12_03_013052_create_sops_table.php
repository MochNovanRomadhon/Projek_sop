<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sops', function (Blueprint $table) {
            $table->id();
            
            // Relasi
            $table->foreignId('user_id')->constrained('users'); // Siapa pengusulnya
            $table->foreignId('unit_id')->constrained('units'); // SOP milik unit mana
            
            // Kolom Data SOP
            $table->string('judul');
            $table->string('nomor_sk')->nullable(); // Bisa null jika masih Draft
            $table->string('file_path'); // Lokasi file PDF
            
            // Jenis SOP (SOP Unit atau SOP AP/Administrasi Pemerintahan)
            $table->enum('jenis', ['SOP', 'SOP AP'])->default('SOP');
            
            // Status Workflow
            // Draft: Baru dibuat pengusul
            // Menunggu Verifikasi: Sudah disubmit ke Humas
            // Disetujui: Sudah OK oleh Verifikator
            // Ditolak: Ada revisi dari Verifikator
            // Arsip: Versi lama (history)
            $table->enum('status', [
                'Draft', 
                'Menunggu Verifikasi', 
                'Disetujui', 
                'Ditolak', 
                'Arsip'
            ])->default('Draft');

            // Feedback jika ditolak
            $table->text('catatan_revisi')->nullable();

            // Tanggal Penting
            $table->date('tanggal_terbit')->nullable();
            $table->date('tanggal_berlaku')->nullable();
            
            // Otomatis dihitung 3 tahun dari tanggal berlaku
            $table->date('tanggal_kadaluarsa')->nullable(); 

            // Versioning / Riwayat
            // Jika ini revisi dari SOP lama, isi ID SOP lama di sini
            $table->foreignId('parent_id')->nullable()->constrained('sops')->nullOnDelete();

            $table->timestamps();
            
            // Indexing untuk performa pencarian
            $table->index(['status', 'unit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sops');
    }
};