<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth; // [1] Tambahkan Import ini

class Sop extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tanggal_terbit' => 'date',
        'tanggal_berlaku' => 'date',
        'tanggal_kadaluarsa' => 'date',
    ];

    // [2] TAMBAHKAN FUNGSI BOOTED INI
    // Fungsi ini akan berjalan otomatis setiap kali SOP baru dibuat
    protected static function booted(): void
    {
        static::creating(function (Sop $sop) {
            // Jika user_id kosong, isi otomatis dengan ID user yang sedang login
            if (empty($sop->user_id) && Auth::check()) {
                $sop->user_id = Auth::id();
            }
        });
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}