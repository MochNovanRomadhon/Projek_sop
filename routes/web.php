<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicSopController;

// ROUTE HALAMAN DEPAN (PUBLIC ACCESS)
Route::get('/', [PublicSopController::class, 'index'])->name('home');
Route::get('/unit/{id}', [PublicSopController::class, 'show'])->name('public.unit');

// ROUTE LOGIN (Ini mengarah ke halaman login Filament jika user mengakses /login)
Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');