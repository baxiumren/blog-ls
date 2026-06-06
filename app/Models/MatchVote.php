<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchVote extends Model
{
    protected $fillable = ['fixture_id', 'choice', 'visitor'];

    public static function tally($fixtureId): array
    {
        $c = static::where('fixture_id', $fixtureId)
            ->selectRaw('choice, count(*) as c')->groupBy('choice')->pluck('c', 'choice');
        $home = (int) ($c['home'] ?? 0);
        $draw = (int) ($c['draw'] ?? 0);
        $away = (int) ($c['away'] ?? 0);
        return ['home' => $home, 'draw' => $draw, 'away' => $away, 'total' => $home + $draw + $away];
    }
}