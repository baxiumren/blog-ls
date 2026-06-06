<?php

namespace App\Console\Commands;

use App\Models\Fixture;
use App\Services\ApiFootball;
use Illuminate\Console\Command;

class SyncTodayScores extends Command
{
    protected $signature = 'sync:today {date?}';
    protected $description = "Update today's fixture scores/status from API (light, 1 call)";

    public function handle(ApiFootball $api): int
    {
        $date = $this->argument('date') ?: now()->toDateString();
        $rows = $api->fixturesByDate($date);
        $updated = 0;

        foreach ($rows as $f) {
            $apiId = $f['fixture']['id'] ?? null;
            if (! $apiId) {
                continue;
            }
            $fixture = Fixture::where('api_id', $apiId)->first();
            if (! $fixture) {
                continue;
            }
            $fixture->update([
                'home_score' => $f['goals']['home'],
                'away_score' => $f['goals']['away'],
                'status'     => $this->mapStatus($f['fixture']['status']['short'] ?? ''),
                'minute'     => $f['fixture']['status']['elapsed'],
            ]);
            $updated++;
        }

        $this->info("Updated {$updated} fixtures for {$date}.");
        return self::SUCCESS;
    }

    private function mapStatus(string $short): string
    {
        return match ($short) {
            '1H', '2H', 'HT', 'ET', 'BT', 'P', 'LIVE', 'INT' => 'live',
            'FT', 'AET', 'PEN' => 'finished',
            default => 'scheduled',
        };
    }
}