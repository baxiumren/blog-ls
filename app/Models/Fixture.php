<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Fixture extends Model
{
    protected $fillable = ['api_id', 'league_id', 'home_team_id', 'away_team_id', 'home_score', 'away_score', 'status', 'minute', 'kickoff_at', 'round'];

    protected $casts = [];

    /** Stored in UTC, displayed in the site's configured timezone (Settings → General → Timezone). */
    protected function kickoffAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value, 'UTC')->setTimezone(Setting::get('timezone') ?: 'UTC') : null,
            set: fn ($value) => $value ? Carbon::parse($value)->utc()->format('Y-m-d H:i:s') : null,
        );
    }

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