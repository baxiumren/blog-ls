<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Team extends Model
{
    protected $fillable = ['league_id', 'name', 'short_name', 'api_id', 'logo_url'];

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }
}