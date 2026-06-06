<?php

namespace Database\Seeders;

use App\Models\Fixture;
use App\Models\League;
use App\Models\Team;
use Illuminate\Database\Seeder;

class FixtureSeeder extends Seeder
{
    public function run(): void
    {
        $team   = fn ($short) => Team::where('short_name', $short)->value('id');
        $league = fn ($code)  => League::where('code', $code)->value('id');

        // [liga, home, away, skorH, skorA, status, menit, jamKickoff(offset)]
        $rows = [
            ['PL', 'ARS', 'CHE', 5, 1, 'live', 67, 0],
            ['PL', 'LIV', 'MCI', 0, 0, 'live', 34, 0],
            ['PL', 'TOT', 'ARS', 1, 2, 'finished', null, -48],
            ['PL', 'MCI', 'CHE', 3, 1, 'finished', null, -72],
            ['PL', 'CHE', 'LIV', 2, 2, 'finished', null, -96],
            ['PL', 'ARS', 'MCI', 0, 1, 'finished', null, -120],
            ['PL', 'TOT', 'LIV', 3, 0, 'finished', null, -144],
            ['LL', 'BAR', 'RMA', 3, 2, 'finished', null, -2],
            ['LL', 'SEV', 'ATM', null, null, 'scheduled', null, 3],
            ['LL', 'RMA', 'SEV', 2, 0, 'finished', null, -50],
            ['LL', 'ATM', 'BAR', 1, 1, 'finished', null, -74],
        ];

        foreach ($rows as [$lg, $h, $a, $hs, $as, $st, $min, $off]) {
            Fixture::create([
                'league_id'    => $league($lg),
                'home_team_id' => $team($h),
                'away_team_id' => $team($a),
                'home_score'   => $hs,
                'away_score'   => $as,
                'status'       => $st,
                'minute'       => $min,
                'kickoff_at'   => now()->addHours($off),
            ]);
        }
    }
}