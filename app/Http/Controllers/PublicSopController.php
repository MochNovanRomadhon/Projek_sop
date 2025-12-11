<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Sop;
use Illuminate\Http\Request;

class PublicSopController extends Controller
{
    // Halaman Utama: Menampilkan Daftar Folder Unit
    public function index()
    {
        // Ambil unit yang punya minimal 1 SOP yang SUDAH DISETUJUI
        // Kita hitung juga jumlah SOP aktifnya
        $units = Unit::withCount(['sops' => function ($query) {
            $query->where('status', 'Disetujui');
        }])->get();

        return view('welcome', compact('units'));
    }

    // Halaman Detail: Menampilkan Daftar SOP dalam satu Unit
    public function show($id)
    {
        $unit = Unit::findOrFail($id);
        
        // Ambil SOP milik unit ini yang statusnya 'Disetujui'
        // Urutkan dari yang terbaru
        $sops = Sop::where('unit_id', $id)
                    ->where('status', 'Disetujui')
                    ->latest()
                    ->get();

        return view('public-unit', compact('unit', 'sops'));
    }
}