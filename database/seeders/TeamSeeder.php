<?php

namespace Database\Seeders;

use App\Models\League;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            'PL' => [
                ['name' => 'Arsenal',   'short_name' => 'ARS'],
                ['name' => 'Chelsea',   'short_name' => 'CHE'],
                ['name' => 'Liverpool', 'short_name' => 'LIV'],
                ['name' => 'Man City',  'short_name' => 'MCI'],
                ['name' => 'Tottenham', 'short_name' => 'TOT'],
            ],
            'LL' => [
                ['name' => 'Barcelona',   'short_name' => 'BAR'],
                ['name' => 'Real Madrid', 'short_name' => 'RMA'],
                ['name' => 'Sevilla',     'short_name' => 'SEV'],
                ['name' => 'Atletico',    'short_name' => 'ATM'],
            ],
        ];

        foreach ($teams as $code => $list) {
            $league = League::where('code', $code)->first();

            foreach ($list as $team) {
                $league->teams()->create($team);
            }
        }
    }
}