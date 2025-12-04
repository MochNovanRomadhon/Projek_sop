<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $guarded = [];

    public function direktorat(): BelongsTo
    {
        return $this->belongsTo(Direktorat::class);
    }

    public function sops(): HasMany
    {
        return $this->hasMany(Sop::class);
    }
    
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}