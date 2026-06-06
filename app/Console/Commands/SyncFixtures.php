<?php

namespace App\Console\Commands;

use App\Models\Fixture;
use App\Models\League;
use App\Models\Team;
use App\Services\ApiFootball;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncFixtures extends Command
{
    protected $signature = 'sync:fixtures';
    protected $description = 'Sync fixtures (jadwal + hasil) from API-Football';

    public function handle(ApiFootball $api)
    {
        // Peta: api_id tim → id lokal (buat nyambungin home/away)
        $teamMap = Team::whereNotNull('api_id')->pluck('id', 'api_id')->all();

        foreach (League::whereNotNull('api_id')->get() as $league) {
            // Cari season aktif liga ini
            $data = $api->league($league->api_id);
            $season = collect($data['seasons'] ?? [])->firstWhere('current', true)['year'] ?? null;
            if (! $season) {
                $this->warn("Skip {$league->name}: season gak ketemu");
                continue;
            }

            $fixtures = $api->fixtures($league->api_id, $season);
            $saved = 0;

            foreach ($fixtures as $f) {
                $homeApi = $f['teams']['home']['id'];
                $awayApi = $f['teams']['away']['id'];

                // Lewati kalau salah satu tim gak ada di DB kita
                if (! isset($teamMap[$homeApi]) || ! isset($teamMap[$awayApi])) {
                    continue;
                }

                Fixture::updateOrCreate(
                    ['api_id' => $f['fixture']['id']],
                    [
                        'league_id'    => $league->id,
                        'home_team_id' => $teamMap[$homeApi],
                        'away_team_id' => $teamMap[$awayApi],
                        'home_score'   => $f['goals']['home'],
                        'away_score'   => $f['goals']['away'],
                        'status'       => $this->mapStatus($f['fixture']['status']['short']),
                        'minute'       => $f['fixture']['status']['elapsed'],
                        'kickoff_at'   => $f['fixture']['date'],
                        'round'        => $f['league']['round'] ?? null,
                    ]
                );
                $saved++;
            }

            $this->info("{$league->name}: {$saved} fixtures (season {$season})");
        }

        Cache::flush();
        $this->info('Cache dibersihkan.');

        $this->info('Selesai!');
        return self::SUCCESS;
    }

    // Terjemahin kode status API → status sederhana kita
    private function mapStatus(string $short): string
    {
        return match ($short) {
            '1H', '2H', 'HT', 'ET', 'BT', 'P', 'LIVE', 'INT' => 'live',
            'FT', 'AET', 'PEN' => 'finished',
            default => 'scheduled', // NS, TBD, PST, CANC, dll
        };
    }
}