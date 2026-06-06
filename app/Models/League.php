<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class League extends Model
{
    protected $fillable = ['name', 'code', 'color', 'api_id', 'logo_url', 'country', 'flag', 'season', 'type', 'priority'];

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function fixtures(): HasMany
    {
        return $this->hasMany(Fixture::class);
    }

    public function standings()
    {
        // 1) Siapin baris kosong buat tiap tim
        $rows = [];
        foreach ($this->teams as $team) {
            $rows[$team->id] = [
                'team' => $team,
                'played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0,
                'gf' => 0, 'ga' => 0, 'points' => 0,
            ];
        }

        // 2) Loop match yang sudah selesai, hitung statistiknya
        foreach ($this->fixtures->where('status', 'finished') as $f) {
            $h = $f->home_team_id;
            $a = $f->away_team_id;
            if (!isset($rows[$h]) || !isset($rows[$a])) continue;

            $rows[$h]['played']++;          $rows[$a]['played']++;
            $rows[$h]['gf'] += $f->home_score; $rows[$h]['ga'] += $f->away_score;
            $rows[$a]['gf'] += $f->away_score; $rows[$a]['ga'] += $f->home_score;

            if ($f->home_score > $f->away_score) {        // home menang
                $rows[$h]['won']++;  $rows[$h]['points'] += 3; $rows[$a]['lost']++;
            } elseif ($f->home_score < $f->away_score) {  // away menang
                $rows[$a]['won']++;  $rows[$a]['points'] += 3; $rows[$h]['lost']++;
            } else {                                       // seri
                $rows[$h]['drawn']++; $rows[$a]['drawn']++;
                $rows[$h]['points']++; $rows[$a]['points']++;
            }
        }

        // 3) Urutkan: poin tertinggi dulu, lalu selisih gol
        return collect($rows)
            ->sortByDesc(fn ($r) => $r['points'] * 1000 + ($r['gf'] - $r['ga']))
            ->values();
    }
}