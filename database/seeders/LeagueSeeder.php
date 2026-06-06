<?php

namespace Database\Seeders;

use App\Models\League;
use Illuminate\Database\Seeder;

class LeagueSeeder extends Seeder
{
    public function run(): void
    {
        $leagues = [
            ['name' => 'Premier League',   'code' => 'PL', 'color' => 'bg-purple-700'],
            ['name' => 'LaLiga',           'code' => 'LL', 'color' => 'bg-red-600'],
            ['name' => 'Serie A',          'code' => 'SA', 'color' => 'bg-blue-700'],
            ['name' => 'Bundesliga',       'code' => 'BL', 'color' => 'bg-red-700'],
            ['name' => 'Ligue 1',          'code' => 'L1', 'color' => 'bg-blue-800'],
            ['name' => 'Champions League', 'code' => 'CL', 'color' => 'bg-blue-600'],
        ];

        foreach ($leagues as $league) {
            League::create($league);
        }
    }
}