<?php

namespace App\Console\Commands;

use App\Models\League;
use App\Models\Team;
use App\Services\ApiFootball;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncLeaguesTeams extends Command
{
    protected $signature = 'sync:leagues-teams';
    protected $description = 'Sync leagues and teams from API-Football';

    public function handle(ApiFootball $api)
    {
        // Cups/internasional DULU (biar tim domestik diklaim balik), terus domestik
        $leagueIds = [
            10, 1, 2, 3, 848,
            39, 140, 135, 78, 61, 88, 94, 144, 179, 203, 197, 218, 207, 119, 103, 113, 106, 235, 333, 210, 286, 71, 128, 253, 262, 239, 265, 98, 292, 169, 307, 305, 188, 274, 233, 200,
            40, 141, 136, 79, 62, 72,
        ];

        // Kode + warna liga lama (biar konsisten); selain ini auto
        $known = [
            10 => ['INT', 'bg-zinc-600'], 1 => ['WC', 'bg-yellow-600'], 2 => ['CL', 'bg-indigo-700'], 3 => ['EL', 'bg-orange-700'],
            39 => ['PL', 'bg-purple-700'], 140 => ['LL', 'bg-orange-600'], 135 => ['SA', 'bg-blue-700'], 78 => ['BL', 'bg-red-700'],
            61 => ['L1', 'bg-blue-900'], 253 => ['MLS', 'bg-sky-800'], 88 => ['ERE', 'bg-orange-500'], 94 => ['POR', 'bg-green-700'],
            307 => ['SPL', 'bg-green-800'], 71 => ['BRA', 'bg-yellow-600'], 262 => ['MX', 'bg-emerald-700'], 274 => ['IDN', 'bg-red-600'],
        ];
        $palette = ['bg-blue-700', 'bg-red-700', 'bg-green-700', 'bg-purple-700', 'bg-orange-600', 'bg-teal-700', 'bg-pink-700', 'bg-indigo-700', 'bg-emerald-700', 'bg-sky-700', 'bg-cyan-700', 'bg-rose-700'];
        $logoOverrides = [1 => '/logos/wc.png'];

        foreach ($leagueIds as $i => $lid) {
            $data = $api->league($lid);
            if (! $data) {
                $this->warn("Liga {$lid} gak ketemu, skip.");
                continue;
            }
            $season = collect($data['seasons'] ?? [])->firstWhere('current', true)['year'] ?? null;
            if (! $season) {
                $this->warn("Liga {$lid} ({$data['league']['name']}) gak ada season aktif, skip.");
                continue;
            }

            [$code, $color] = $known[$lid] ?? ['L' . $lid, $palette[$i % count($palette)]];

            $league = League::updateOrCreate(
                ['api_id' => $lid],
                [
                    'name'     => $data['league']['name'],
                    'code'     => $code,
                    'color'    => $color,
                    'logo_url' => $logoOverrides[$lid] ?? ($data['league']['logo'] ?? null),
                    'country'  => $data['country']['name'] ?? null,
                    'flag'     => $data['country']['flag'] ?? null,
                    'season'   => $season,
                    'type'     => $data['league']['type'] ?? null,
                ]
            );

            $teams = $api->teams($lid, $season);
            foreach ($teams as $t) {
                Team::updateOrCreate(
                    ['api_id' => $t['team']['id']],
                    [
                        'league_id'  => $league->id,
                        'name'       => $t['team']['name'],
                        'short_name' => $t['team']['code'] ?? strtoupper(substr($t['team']['name'], 0, 3)),
                        'logo_url'   => $t['team']['logo'],
                    ]
                );
            }

            $this->info("{$league->name} ({$data['country']['name']}, season {$season}) — " . count($teams) . ' tim');
        }

        Cache::flush();
        $this->info('Selesai! Liga: ' . League::count() . ', tim: ' . Team::count());
        return self::SUCCESS;
    }
}