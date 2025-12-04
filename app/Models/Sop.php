<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sop extends Model
{
    protected $guarded = [];

    // Cast tanggal agar otomatis jadi object Carbon (mudah diformat di Filament)
    protected $casts = [
        'tanggal_terbit' => 'date',
        'tanggal_berlaku' => 'date',
        'tanggal_kadaluarsa' => 'date',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function pengusul(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi untuk melihat versi sebelumnya (Parent)
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Sop::class, 'parent_id');
    }

    // Relasi untuk melihat revisi-revisi (Children)
    public function children(): HasMany
    {
        return $this->hasMany(Sop::class, 'parent_id');
    }
}