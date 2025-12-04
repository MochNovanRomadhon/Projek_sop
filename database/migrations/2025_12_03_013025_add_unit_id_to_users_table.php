<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Nullable karena Admin/Humas mungkin tidak terikat unit spesifik
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            
            // Kolom aktif untuk memblokir user jika resign
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'is_active']);
        });
    }
};