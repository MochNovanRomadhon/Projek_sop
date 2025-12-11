<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Kode konfigurasi polling dihapus karena menyebabkan error pada versi ini.
        // Filament akan menggunakan polling default (30 detik).
    }
}