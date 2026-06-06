<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fixture;
use App\Models\Team;
use App\Services\ApiFootball;
use Illuminate\Support\Facades\Cache;

class PlayerController extends Controller
{
    public function show($id, ApiFootball $api, Request $request)
    {
        $id = (int) $id;

        // Daftar musim dari karir (buat season selector)
        $careerRaw = Cache::remember("player.{$id}.teams", 604800, fn () => $api->playerTeams($id));
        $seasonsList = collect($careerRaw)->flatMap(fn ($c) => $c['seasons'] ?? [])->unique()->sortDesc()->take(8)->values();
        if ($seasonsList->isEmpty()) {
            $seasonsList = collect([2025]);
        }

        $reqSeason = (int) $request->query('season', 0);
        if ($seasonsList->contains($reqSeason)) {
            $season = $reqSeason;
            $data = Cache::remember("player.{$id}.{$season}", 86400, fn () => $api->player($id, $season));
        } else {
            $season = 2025;
            $data = Cache::remember("player.{$id}.{$season}", 86400, fn () => $api->player($id, $season));
            if (! $data) {
                $season = 2026;
                $data = Cache::remember("player.{$id}.{$season}", 86400, fn () => $api->player($id, $season));
            }
        }
        abort_if(! $data, 404);

        $p = $data['player'];
        $stats = collect($data['statistics'] ?? [])->filter(fn ($s) => ($s['games']['appearences'] ?? 0) > 0);

        $rows = $stats->map(fn ($s) => [
            'league' => $s['league']['name'] ?? '', 'leagueLogo' => $s['league']['logo'] ?? null,
            'team' => $s['team']['name'] ?? '', 'teamId' => $s['team']['id'] ?? null, 'teamLogo' => $s['team']['logo'] ?? null,
            'apps' => $s['games']['appearences'] ?? 0, 'position' => $s['games']['position'] ?? null,
            'goals' => $s['goals']['total'] ?? 0, 'assists' => $s['goals']['assists'] ?? 0,
            'minutes' => $s['games']['minutes'] ?? 0,
            'rating' => $s['games']['rating'] ? round((float) $s['games']['rating'], 2) : null,
        ])->sortByDesc('apps')->values();

        $primary = $rows->first();
        $teamLocalId = $primary ? optional(Team::where('api_id', $primary['teamId'])->first())->id : null;
        // Warna jersey tim (dari lineups laga terakhir) → background header
        $teamColor = null;
        if ($teamLocalId && $primary) {
            $teamColor = Cache::remember("team.{$primary['teamId']}.jerseycolor", 604800, function () use ($teamLocalId, $api) {
                $t = Team::find($teamLocalId);
                $last = Fixture::where('status', 'finished')->whereNotNull('api_id')
                    ->where(fn ($q) => $q->where('home_team_id', $teamLocalId)->orWhere('away_team_id', $teamLocalId))
                    ->orderByDesc('kickoff_at')->first();
                if (! $last || ! $t) {
                    return null;
                }
                $lus = Cache::remember("fixture.{$last->api_id}.lineups", 86400, fn () => $api->lineups($last->api_id));
                foreach ($lus as $lu) {
                    if ((int) ($lu['team']['id'] ?? 0) === (int) $t->api_id) {
                        return $lu['team']['colors']['player']['primary'] ?? null;
                    }
                }
                return null;
            });
        }
        $darkText = false;
        if ($teamColor && strlen($teamColor) === 6 && ctype_xdigit($teamColor)) {
            $lum = 0.299 * hexdec(substr($teamColor, 0, 2)) + 0.587 * hexdec(substr($teamColor, 2, 2)) + 0.114 * hexdec(substr($teamColor, 4, 2));
            $darkText = $lum > 150;
        } else {
            $teamColor = null;
        }

        $sum = fn ($path) => (int) $stats->sum(fn ($s) => data_get($s, $path) ?? 0);

        $bio = [
            'height' => $p['height'] ?? null, 'weight' => $p['weight'] ?? null,
            'age' => $p['age'] ?? null, 'birth' => $p['birth']['date'] ?? null,
            'country' => $p['nationality'] ?? null,
            'position' => $primary['position'] ?? null,
            'number' => $stats->map(fn ($s) => $s['games']['number'] ?? null)->filter()->first(),
        ];

        $totMin = max(1, $sum('games.minutes'));
        $wRating = $stats->sum(fn ($s) => ((float) ($s['games']['rating'] ?? 0)) * ($s['games']['minutes'] ?? 0));
        $summary = [
            'goals' => $sum('goals.total'), 'assists' => $sum('goals.assists'),
            'started' => $sum('games.lineups'), 'matches' => $sum('games.appearences'),
            'minutes' => $sum('games.minutes'),
            'rating' => $wRating ? round($wRating / $totMin, 2) : null,
            'yellow' => $sum('cards.yellow'), 'red' => $sum('cards.red') + $sum('cards.yellowred'),
        ];

        $pacc = $sum('passes.total') ? round($stats->sum(fn ($s) => ((int) ($s['passes']['accuracy'] ?? 0)) * ($s['passes']['total'] ?? 0)) / max(1, $sum('passes.total'))) : null;
        $statGroups = [
            'Shooting' => [['Shots', $sum('shots.total')], ['Shots on target', $sum('shots.on')], ['Goals', $sum('goals.total')], ['Penalty goals', $sum('penalty.scored')]],
            'Passing' => [['Assists', $sum('goals.assists')], ['Key passes', $sum('passes.key')], ['Passes', $sum('passes.total')], ['Pass accuracy %', $pacc]],
            'Possession' => [['Dribbles', $sum('dribbles.attempts')], ['Dribbles won', $sum('dribbles.success')], ['Duels', $sum('duels.total')], ['Duels won', $sum('duels.won')], ['Fouls drawn', $sum('fouls.drawn')]],
            'Defending' => [['Tackles', $sum('tackles.total')], ['Interceptions', $sum('tackles.interceptions')], ['Blocks', $sum('tackles.blocks')], ['Fouls committed', $sum('fouls.committed')]],
            'Discipline' => [['Yellow cards', $sum('cards.yellow')], ['Red cards', $sum('cards.red') + $sum('cards.yellowred')]],
        ];

        $career = collect($careerRaw)->map(function ($c) {
            $seasons = collect($c['seasons'] ?? []);
            return ['team' => $c['team']['name'] ?? '', 'logo' => $c['team']['logo'] ?? null, 'from' => $seasons->min(), 'to' => $seasons->max()];
        })->filter(fn ($c) => $c['team'])->sortByDesc('to')->values();

        $trophiesRaw = Cache::remember("player.{$id}.trophies", 604800, fn () => $api->playerTrophies($id));
        $trophies = collect($trophiesRaw)->map(fn ($t) => ['league' => $t['league'] ?? '', 'season' => $t['season'] ?? '', 'place' => $t['place'] ?? ''])->filter(fn ($t) => $t['league']);
        $trophyWins = $trophies->filter(fn ($t) => stripos($t['place'], 'winner') !== false)->values();
        $trophyRunner = $trophies->filter(fn ($t) => stripos($t['place'], 'winner') === false)->values();

        // About — teks otomatis dari data
        $posWord = ['Attacker' => 'forward', 'Defender' => 'defender', 'Midfielder' => 'midfielder', 'Goalkeeper' => 'goalkeeper'][$bio['position']] ?? 'player';
        $about = [];
        $about[] = sprintf('%s is a %s%s%s.',
            $p['name'],
            $bio['age'] ? $bio['age'] . '-year-old ' : '',
            $posWord,
            $primary ? ' for ' . $primary['team'] : ''
        );
        if ($primary) {
            $about[] = sprintf('In the %d/%02d season, %s has recorded %d goals and %d assists in %d appearances (%s minutes)%s.',
                $season, ($season + 1) % 100, $p['name'],
                $summary['goals'], $summary['assists'], $summary['matches'],
                number_format($summary['minutes']),
                $summary['rating'] ? ', with an average rating of ' . $summary['rating'] : ''
            );
        }

        // Recent matches — log per-laga dari tim utama pemain
        $recentMatches = collect();
        if ($teamLocalId) {
            $fixtures = Fixture::with(['homeTeam', 'awayTeam'])
                ->where('status', 'finished')->whereNotNull('api_id')
                ->where(fn ($q) => $q->where('home_team_id', $teamLocalId)->orWhere('away_team_id', $teamLocalId))
                ->orderByDesc('kickoff_at')->limit(8)->get();
            foreach ($fixtures as $f) {
                $pd = Cache::remember("fixture.{$f->api_id}.players", 86400, fn () => $api->players($f->api_id));
                $line = null;
                foreach ($pd as $tb) {
                    foreach ($tb['players'] ?? [] as $pl) {
                        if ((int) ($pl['player']['id'] ?? 0) === $id) {
                            $line = $pl['statistics'][0] ?? null;
                            break 2;
                        }
                    }
                }
                if (! $line) {
                    continue;
                }
                $isHome = $f->home_team_id == $teamLocalId;
                $opp = $isHome ? $f->awayTeam : $f->homeTeam;
                $gf = $isHome ? $f->home_score : $f->away_score;
                $ga = $isHome ? $f->away_score : $f->home_score;
                $recentMatches->push([
                    'id' => $f->id,
                    'date' => $f->kickoff_at,
                    'opp' => $opp->short_name ?? $opp->name,
                    'oppLogo' => $opp->logo_url,
                    'res' => $gf > $ga ? 'W' : ($gf < $ga ? 'L' : 'D'),
                    'score' => $f->home_score . '-' . $f->away_score,
                    'minutes' => $line['games']['minutes'] ?? 0,
                    'goals' => $line['goals']['total'] ?? 0,
                    'assists' => $line['goals']['assists'] ?? 0,
                    'rating' => ($line['games']['rating'] ?? null) ? round((float) $line['games']['rating'], 2) : null,
                ]);
            }
        }

        return view('pages.player', compact('p', 'rows', 'summary', 'bio', 'statGroups', 'primary', 'season', 'teamLocalId', 'career', 'trophyWins', 'trophyRunner', 'teamColor', 'darkText', 'about', 'recentMatches', 'seasonsList'));
    }
}