<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $fillable = ['nama', 'direktorat_id']; // Pastikan ada direktorat_id di tabel units

    public function direktorat(): BelongsTo
    {
        return $this->belongsTo(Direktorat::class);
    }

    public function sops(): HasMany
    {
        return $this->hasMany(Sop::class);
    }

    // Relasi khusus untuk menghitung jenis 'SOP'
    public function sops_murni(): HasMany
    {
        return $this->hasMany(Sop::class)->where('jenis', 'SOP');
    }

    // Relasi khusus untuk menghitung jenis 'SOP AP'
    public function sop_aps(): HasMany
    {
        return $this->hasMany(Sop::class)->where('jenis', 'SOP AP');
    }
}