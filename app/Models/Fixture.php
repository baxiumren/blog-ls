<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fixture extends Model
{
    protected $fillable = ['api_id', 'league_id', 'home_team_id', 'away_team_id', 'home_score', 'away_score', 'status', 'minute', 'kickoff_at', 'round'];

    protected $casts = [
        'kickoff_at' => 'datetime',
    ];

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function prediction() { return $this->hasOne(Prediction::class); }
    
    public function highlight()
    {
        return $this->hasOne(Highlight::class);
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }
}