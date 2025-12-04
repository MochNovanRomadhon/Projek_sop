<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Direktorat extends Model
{
    // Agar bisa diisi semua kolom (mass assignment)
    protected $guarded = [];

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }
}