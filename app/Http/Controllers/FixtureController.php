<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Services\ApiFootball;
use Illuminate\Support\Facades\Cache;

class FixtureController extends Controller
{
    public function show($id, ApiFootball $api)
    {
        $fixture = Fixture::with(['homeTeam', 'awayTeam', 'league'])->findOrFail($id);

        // Form 5 laga terakhir tiap tim (dari DB)
        $homeForm = $this->teamForm($fixture->home_team_id, $fixture->kickoff_at);
        $awayForm = $this->teamForm($fixture->away_team_id, $fixture->kickoff_at);

        // Head-to-head (riwayat pertemuan)
        $h2h = [];
        // Prediksi (Who will win)
        $prediction = null;
        if ($fixture->api_id) {
            $prediction = Cache::remember("fixture.{$fixture->api_id}.pred", 86400, fn () => $api->predictions($fixture->api_id));
        }
        if ($fixture->homeTeam->api_id && $fixture->awayTeam->api_id) {
            $hId = $fixture->homeTeam->api_id;
            $aId = $fixture->awayTeam->api_id;
            $h2h = Cache::remember("h2h.{$hId}-{$aId}", 86400, fn () => $api->headToHead($hId, $aId, 10));
        }

        $events = [];
        $statRows = [];
        $lineups = [];
        $info = [];
        $pstats = [];
        $subbedIn = [];
        $playerEvents = [];
        $lineupPredicted = false;

        if ($fixture->api_id && $fixture->status !== 'scheduled') {
            $ttl = $fixture->status === 'live' ? 30 : 86400;

            $events = Cache::remember("fixture.{$fixture->api_id}.events", $ttl, fn () => $api->events($fixture->api_id));
            foreach ($events as $e) {
                if (($e['type'] ?? '') === 'subst' && ! empty($e['assist']['id'])) {
                    $subbedIn[$e['assist']['id']] = $e['time']['elapsed'] ?? null;
                }

                foreach ($events as $e) {
                    $pid = $e['player']['id'] ?? null;
                    if (! $pid) {
                        continue;
                    }
                    $type = $e['type'] ?? '';
                    if ($type === 'Goal') {
                        if (($e['detail'] ?? '') === 'Own Goal') {
                            $playerEvents[$pid]['og'] = true;
                        } else {
                            $playerEvents[$pid]['goals'] = ($playerEvents[$pid]['goals'] ?? 0) + 1;
                        }
                    } elseif ($type === 'Card') {
                        if (str_contains($e['detail'] ?? '', 'Red')) {
                            $playerEvents[$pid]['red'] = true;
                        } else {
                            $playerEvents[$pid]['yellow'] = true;
                        }
                    } elseif ($type === 'subst') {
                        $playerEvents[$pid]['subOff'] = true;
                    }
                }
            }

            $stats = Cache::remember("fixture.{$fixture->api_id}.stats", $ttl, fn () => $api->statistics($fixture->api_id));
            $statRows = $this->buildStatRows($stats, $fixture);

            $lineups = Cache::remember("fixture.{$fixture->api_id}.lineups", $ttl, fn () => $api->lineups($fixture->api_id));
            if (count($lineups) === 2 && (int) ($lineups[0]['team']['id'] ?? 0) !== (int) $fixture->homeTeam->api_id) {
                $lineups = array_reverse($lineups);
            }

            // Rating + foto pemain (buat lineup ala FotMob)
            $playersData = Cache::remember("fixture.{$fixture->api_id}.players", $ttl, fn () => $api->players($fixture->api_id));
            foreach ($playersData as $teamBlock) {
                    foreach ($teamBlock['players'] ?? [] as $p) {
                        $pid = $p['player']['id'] ?? null;
                        if (! $pid) {
                            continue;
                        }
                        $s0 = $p['statistics'][0] ?? [];
                        $g = $s0['games'] ?? [];
                        $pstats[$pid] = [
                            'name'            => $p['player']['name'] ?? '',
                            'photo'           => $p['player']['photo'] ?? null,
                            'rating'          => $g['rating'] ?? null,
                            'captain'         => $g['captain'] ?? false,
                            'minutes'         => $g['minutes'] ?? null,
                            'position'        => $g['position'] ?? null,
                            'goals'           => $s0['goals']['total'] ?? null,
                            'assists'         => $s0['goals']['assists'] ?? null,
                            'saves'           => $s0['goals']['saves'] ?? null,
                            'shots'           => $s0['shots']['total'] ?? null,
                            'shots_on'        => $s0['shots']['on'] ?? null,
                            'passes'          => $s0['passes']['total'] ?? null,
                            'key_passes'      => $s0['passes']['key'] ?? null,
                            'pass_acc'        => $s0['passes']['accuracy'] ?? null,
                            'tackles'         => $s0['tackles']['total'] ?? null,
                            'interceptions'   => $s0['tackles']['interceptions'] ?? null,
                            'duels'           => $s0['duels']['total'] ?? null,
                            'duels_won'       => $s0['duels']['won'] ?? null,
                            'dribbles'        => $s0['dribbles']['attempts'] ?? null,
                            'dribbles_won'    => $s0['dribbles']['success'] ?? null,
                            'fouls_drawn'     => $s0['fouls']['drawn'] ?? null,
                            'fouls_committed' => $s0['fouls']['committed'] ?? null,
                        ];
                    }
                }

            $detail = Cache::remember("fixture.{$fixture->api_id}.detail", $ttl, fn () => $api->fixture($fixture->api_id));
            $info = [
                'venue'   => $detail['fixture']['venue']['name'] ?? null,
                'city'    => $detail['fixture']['venue']['city'] ?? null,
                'referee' => $detail['fixture']['referee'] ?? null,
                'round'   => $detail['league']['round'] ?? null,
                'penHome' => $detail['score']['penalty']['home'] ?? null,
                'penAway' => $detail['score']['penalty']['away'] ?? null,
            ];
        }

        // Prediksi susunan buat match belum main — dari laga terakhir tiap tim
        if (empty($lineups) && $fixture->status === 'scheduled') {
            $homeLu = $this->predictedLineup($fixture->homeTeam, $api);
            $awayLu = $this->predictedLineup($fixture->awayTeam, $api);
            if ($homeLu && $awayLu) {
                $lineups = [$homeLu, $awayLu];
                $lineupPredicted = true;
                foreach ($lineups as $lu) {
                    foreach (array_merge($lu['startXI'] ?? [], $lu['substitutes'] ?? []) as $p) {
                        $pid = $p['player']['id'] ?? null;
                        if (! $pid) {
                            continue;
                        }
                        $pstats[$pid] = $pstats[$pid] ?? [
                            'name'    => $p['player']['name'] ?? '',
                            'photo'   => "https://media.api-sports.io/football/players/{$pid}.png",
                            'rating'  => null,
                            'captain' => false,
                        ];
                    }
                }
            }
        }

        $standings = collect();
        $hasTable = $fixture->league->type === 'League';
        if ($hasTable) {
            $standings = Cache::remember("standings.{$fixture->league->code}", 600, fn () => $fixture->league->standings());
        }

        $pollCounts = \App\Models\MatchVote::tally($fixture->id);

        $matchPrediction = \App\Models\Prediction::where('fixture_id', $fixture->id)->published()->first();
        $highlight = $fixture->highlight;

        return view('pages.match', compact('fixture', 'events', 'statRows', 'lineups', 'standings', 'info', 'homeForm', 'awayForm', 'h2h', 'pstats', 'subbedIn', 'prediction', 'playerEvents', 'pollCounts', 'matchPrediction', 'highlight'));
    }

    // 5 hasil terakhir tim (W/D/L), urut lama → baru
    private function teamForm(int $teamId, $before): array
    {
        return Fixture::where('status', 'finished')
            ->where('kickoff_at', '<', $before)
            ->where(fn ($q) => $q->where('home_team_id', $teamId)->orWhere('away_team_id', $teamId))
            ->orderByDesc('kickoff_at')
            ->limit(5)
            ->get()
            ->map(function ($f) use ($teamId) {
                $isHome = $f->home_team_id === $teamId;
                $gf = $isHome ? $f->home_score : $f->away_score;
                $ga = $isHome ? $f->away_score : $f->home_score;
                return $gf > $ga ? 'W' : ($gf < $ga ? 'L' : 'D');
            })
            ->reverse()
            ->values()
            ->all();
    }

    // Ambil susunan dari laga terakhir 1 tim (buat prediksi)
    private function predictedLineup($team, ApiFootball $api): ?array
    {
        if (! $team->api_id) {
            return null;
        }
        $last = Fixture::where('status', 'finished')
            ->whereNotNull('api_id')
            ->where(fn ($q) => $q->where('home_team_id', $team->id)->orWhere('away_team_id', $team->id))
            ->orderByDesc('kickoff_at')
            ->first();
        if (! $last) {
            return null;
        }
        $lus = Cache::remember("fixture.{$last->api_id}.lineups", 86400, fn () => $api->lineups($last->api_id));
        foreach ($lus as $lu) {
            if ((int) ($lu['team']['id'] ?? 0) === (int) $team->api_id) {
                return $lu;
            }
        }
        return null;
    }

    private function buildStatRows(array $stats, Fixture $fixture): array
    {
        if (count($stats) < 2) {
            return [];
        }

        $byTeam = [];
        foreach ($stats as $s) {
            $byTeam[$s['team']['id']] = collect($s['statistics'])->pluck('value', 'type')->all();
        }

        $home = $byTeam[$fixture->homeTeam->api_id] ?? [];
        $away = $byTeam[$fixture->awayTeam->api_id] ?? [];

        $rows = [];
        foreach ($home as $type => $value) {
            $rows[] = ['type' => $type, 'home' => $value, 'away' => $away[$type] ?? null];
        }

        return $rows;
    }
}