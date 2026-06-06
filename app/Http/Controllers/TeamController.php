<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Fixture;
use App\Services\ApiFootball;
use Illuminate\Support\Facades\Cache;

class TeamController extends Controller
{
    public function show($id, ApiFootball $api)
    {
        $team = Team::with('league')->findOrFail($id);

        $base = fn () => Fixture::with(['homeTeam', 'awayTeam', 'league'])
            ->where(fn ($q) => $q->where('home_team_id', $id)->orWhere('away_team_id', $id));

        $upcoming  = $base()->where('status', 'scheduled')->orderBy('kickoff_at')->limit(10)->get();
        $recent    = $base()->where('status', 'finished')->orderByDesc('kickoff_at')->limit(10)->get();
        $nextMatch = $upcoming->first();

        $formMatches = $recent->take(5)->map(function ($f) use ($id) {
            $isHome = $f->home_team_id == $id;
            $opp = $isHome ? $f->awayTeam : $f->homeTeam;
            $gf = $isHome ? $f->home_score : $f->away_score;
            $ga = $isHome ? $f->away_score : $f->home_score;
            return [
                'opp'   => $opp,
                'score' => $f->home_score . ' - ' . $f->away_score,
                'res'   => $gf > $ga ? 'W' : ($gf < $ga ? 'L' : 'D'),
                'id'    => $f->id,
            ];
        })->values();

        // Last starting XI (dari laga terakhir)
        $lastXI = null;
        $xiStats = [];
        $lastFixture = $recent->first();
        if ($lastFixture && $lastFixture->api_id && $team->api_id) {
            $lus = Cache::remember("fixture.{$lastFixture->api_id}.lineups", 86400, fn () => $api->lineups($lastFixture->api_id));
            foreach ($lus as $lu) {
                if ((int) ($lu['team']['id'] ?? 0) === (int) $team->api_id) {
                    $lastXI = $lu;
                    break;
                }
            }
            $players = Cache::remember("fixture.{$lastFixture->api_id}.players", 86400, fn () => $api->players($lastFixture->api_id));
            foreach ($players as $tb) {
                if ((int) ($tb['team']['id'] ?? 0) !== (int) $team->api_id) {
                    continue;
                }
                foreach ($tb['players'] ?? [] as $p) {
                    $pid = $p['player']['id'] ?? null;
                    if (! $pid) {
                        continue;
                    }
                    $g = $p['statistics'][0]['games'] ?? [];
                    $xiStats[$pid] = [
                        'name'    => $p['player']['name'] ?? '',
                        'photo'   => $p['player']['photo'] ?? null,
                        'rating'  => $g['rating'] ?? null,
                        'captain' => $g['captain'] ?? false,
                    ];
                }
            }
        }

        // Stadion + tahun berdiri (cache lama, jarang berubah)
        $venue = null;
        $founded = null;
        if ($team->api_id) {
            $td = Cache::remember("team.{$team->api_id}.info", 604800, fn () => $api->team($team->api_id));
            $venue = $td['venue'] ?? null;
            $founded = $td['team']['founded'] ?? null;
        }

        $standings = collect();
        $hasTable = $team->league->type === 'League';
        if ($hasTable) {
            $standings = Cache::remember("standings.{$team->league->code}", 600, fn () => $team->league->standings());
        }

        $country = $team->league->country ?? $team->league->name;

        // Top pemain (rated / scorers / assists)
        $season = $team->league->season ?? 2025;

        $topRated = collect();
        $topScorers = collect();
        $topAssists = collect();
        if ($team->api_id) {
            $squad = Cache::remember("team.{$team->api_id}.squad.{$season}", 86400, fn () => $api->squadStats($team->api_id, $season));
            $rows = collect($squad)->map(function ($p) {
                $st = $p['statistics'][0] ?? [];
                return [
                    'name'    => $p['player']['name'] ?? '',
                    'photo'   => $p['player']['photo'] ?? null,
                    'apps'    => $st['games']['appearences'] ?? 0,
                    'minutes' => $st['games']['minutes'] ?? 0,
                    'goals'   => $st['goals']['total'] ?? 0,
                    'assists' => $st['goals']['assists'] ?? 0,
                    'rating'  => $st['games']['rating'] ? round((float) $st['games']['rating'], 2) : null,
                ];
            });
            $topScorers = $rows->filter(fn ($r) => $r['goals'] > 0)->sortByDesc('goals')->take(3)->values();
            $topAssists = $rows->filter(fn ($r) => $r['assists'] > 0)->sortByDesc('assists')->take(3)->values();
            $topRated   = $rows->filter(fn ($r) => $r['rating'] && $r['apps'] >= 3)->sortByDesc('rating')->take(3)->values();
        }
                // Pelatih + performa per musim (2023..sekarang)
                $coach = null;
                $seasonStats = [];
                if ($team->api_id) {
                    $coaches = Cache::remember("team.{$team->api_id}.coachs", 604800, fn () => $api->coachs($team->api_id));
                    $tenures = [];
                    foreach ($coaches as $c) {
                        foreach ($c['career'] ?? [] as $cr) {
                            if ((int) ($cr['team']['id'] ?? 0) === (int) $team->api_id) {
                                $tenures[] = [
                                    'name'        => $c['name'] ?? '',
                                    'photo'       => $c['photo'] ?? null,
                                    'nationality' => $c['nationality'] ?? null,
                                    'age'         => $c['age'] ?? null,
                                    'start'       => $cr['start'] ?? '0000-00-00',
                                    'end'         => $cr['end'] ?? null,
                                ];
                            }
                        }
                    }
                    $cur = collect($tenures)->first(fn ($x) => empty($x['end']));
                    if ($cur) {
                        $coach = [
                            'name'        => $cur['name'],
                            'photo'       => $cur['photo'],
                            'nationality' => $cur['nationality'],
                            'age'         => $cur['age'],
                            'since'       => $cur['start'],
                        ];
                    }
        
                    for ($yr = 2023; $yr <= $season; $yr++) {
                        $fx = Cache::remember("team.{$team->api_id}.fixtures.{$yr}", 604800, fn () => $api->teamFixtures($team->api_id, $yr));
                        $w = $d = $l = 0;
                        $coachCount = [];
                        foreach ($fx as $f) {
                            if (($f['fixture']['status']['short'] ?? '') !== 'FT') {
                                continue;
                            }
                            $home = (int) ($f['teams']['home']['id'] ?? 0) === (int) $team->api_id;
                            $gf = $home ? $f['goals']['home'] : $f['goals']['away'];
                            $ga = $home ? $f['goals']['away'] : $f['goals']['home'];
                            if ($gf > $ga) { $w++; } elseif ($gf < $ga) { $l++; } else { $d++; }
                            $date = substr($f['fixture']['date'] ?? '', 0, 10);
                            foreach ($tenures as $tn) {
                                if ($tn['start'] <= $date && (empty($tn['end']) || $tn['end'] >= $date)) {
                                    $coachCount[$tn['name']] = ($coachCount[$tn['name']] ?? 0) + 1;
                                    break;
                                }
                            }
                        }
                        $tot = $w + $d + $l;
                        if ($tot === 0) {
                            continue;
                        }
                        arsort($coachCount);
                        $mainName = array_key_first($coachCount) ?: null;
                        $seasonStats[] = [
                            'label'      => sprintf('%02d/%02d', $yr % 100, ($yr + 1) % 100),
                            'winpct'     => (int) round($w / $tot * 100),
                            'ppg'        => round(($w * 3 + $d) / $tot, 1),
                            'coach'      => $mainName,
                            'coachPhoto' => collect($tenures)->firstWhere('name', $mainName)['photo'] ?? null,
                            'w'          => $w,
                            'd'          => $d,
                            'l'          => $l,
                        ];
                    }
                }
        // Squad: gabung /players/squads (nomor+posisi) + squadStats (negara+tinggi)
        $squadGroups = [];
        if ($team->api_id) {
            $rawSquad = Cache::remember("team.{$team->api_id}.squadlist", 604800, fn () => $api->squad($team->api_id));

            // info tambahan dari squadStats, keyed by player id
            $extra = [];
            foreach ($squad as $sp) {
                $pid = $sp['player']['id'] ?? null;
                if ($pid) {
                    $extra[$pid] = [
                        'name'        => $sp['player']['name'] ?? null,
                        'nationality' => $sp['player']['nationality'] ?? null,
                        'height'      => $sp['player']['height'] ?? null,
                    ];
                }
            }

            $posMap = ['Goalkeeper' => 'Goalkeepers', 'Defender' => 'Defenders', 'Midfielder' => 'Midfielders', 'Attacker' => 'Forwards'];
            $grouped = ['Goalkeepers' => [], 'Defenders' => [], 'Midfielders' => [], 'Forwards' => []];
            foreach ($rawSquad as $p) {
                $pid = $p['id'] ?? null;
                $group = $posMap[$p['position'] ?? ''] ?? 'Forwards';
                $grouped[$group][] = [
                    'id'          => $pid,
                    'name'        => $extra[$pid]['name'] ?? ($p['name'] ?? ''),
                    'photo'       => $p['photo'] ?? null,
                    'number'      => $p['number'] ?? null,
                    'age'         => $p['age'] ?? null,
                    'nationality' => $extra[$pid]['nationality'] ?? null,
                    'height'      => $extra[$pid]['height'] ?? null,
                ];
            }

            // urutkan tiap grup by nomor punggung, buang grup kosong
            foreach ($grouped as $k => $list) {
                usort($list, fn ($a, $b) => ($a['number'] ?? 999) <=> ($b['number'] ?? 999));
                $grouped[$k] = $list;
            }
            $squadGroups = array_filter($grouped, fn ($g) => count($g) > 0);
        }

            // Player stats: leaderboard beberapa kategori (top 5)
            $playerLeaders = [];
            if ($team->api_id && isset($rows) && $rows->isNotEmpty()) {
                $withGA = $rows->map(fn ($r) => $r + ['ga' => $r['goals'] + $r['assists']]);
                $cards = [
                    ['title' => 'Goals',           'key' => 'goals',   'rows' => $rows->filter(fn ($r) => $r['goals'] > 0)->sortByDesc('goals')->take(5)->values()],
                    ['title' => 'Assists',         'key' => 'assists', 'rows' => $rows->filter(fn ($r) => $r['assists'] > 0)->sortByDesc('assists')->take(5)->values()],
                    ['title' => 'Goals + Assists', 'key' => 'ga',      'rows' => $withGA->filter(fn ($r) => $r['ga'] > 0)->sortByDesc('ga')->take(5)->values()],
                    ['title' => 'Average rating',  'key' => 'rating',  'rows' => $rows->filter(fn ($r) => $r['rating'] && $r['apps'] >= 3)->sortByDesc('rating')->take(5)->values()],
                    ['title' => 'Appearances',     'key' => 'apps',    'rows' => $rows->filter(fn ($r) => $r['apps'] > 0)->sortByDesc('apps')->take(5)->values()],
                    ['title' => 'Minutes played',  'key' => 'minutes', 'rows' => $rows->filter(fn ($r) => $r['minutes'] > 0)->sortByDesc('minutes')->take(5)->values()],
                ];
                $playerLeaders = array_values(array_filter($cards, fn ($c) => $c['rows']->isNotEmpty()));
            }

        // Team stats (dari /teams/statistics — 1 req, cached)
        $teamStats = null;
        if ($team->api_id && $team->league->api_id) {
            $ts = Cache::remember("team.{$team->api_id}.stats.{$season}", 86400, fn () => $api->teamStatistics($team->league->api_id, $team->api_id, $season));
            if (! empty($ts['fixtures']['played']['total'])) {
                $sumCards = function ($arr) {
                    $t = 0;
                    foreach ($arr ?? [] as $b) {
                        $t += $b['total'] ?? 0;
                    }
                    return $t;
                };
                $teamStats = [
                    'played'      => $ts['fixtures']['played']['total'] ?? 0,
                    'wins'        => $ts['fixtures']['wins']['total'] ?? 0,
                    'draws'       => $ts['fixtures']['draws']['total'] ?? 0,
                    'loses'       => $ts['fixtures']['loses']['total'] ?? 0,
                    'gf'          => $ts['goals']['for']['total']['total'] ?? 0,
                    'gfAvg'       => $ts['goals']['for']['average']['total'] ?? '0',
                    'ga'          => $ts['goals']['against']['total']['total'] ?? 0,
                    'gaAvg'       => $ts['goals']['against']['average']['total'] ?? '0',
                    'cleanSheet'  => $ts['clean_sheet']['total'] ?? 0,
                    'failedScore' => $ts['failed_to_score']['total'] ?? 0,
                    'biggestWin'  => $ts['biggest']['wins']['home'] ?? ($ts['biggest']['wins']['away'] ?? null),
                    'streakWins'  => $ts['biggest']['streak']['wins'] ?? 0,
                    'yellow'      => $sumCards($ts['cards']['yellow'] ?? []),
                    'red'         => $sumCards($ts['cards']['red'] ?? []),
                    'penScored'   => $ts['penalty']['scored']['total'] ?? 0,
                    'penTotal'    => $ts['penalty']['total'] ?? 0,
                    'formation'   => $ts['lineups'][0]['formation'] ?? null,
                ];
            }
        }

        // Transfers masuk & keluar (difilter noise + dedup)
        $transfersIn = collect();
        $transfersOut = collect();
        if ($team->api_id) {
            $raw = Cache::remember("team.{$team->api_id}.transfers", 86400, fn () => $api->transfers($team->api_id));
            $rows = collect();
            foreach ($raw as $pl) {
                $pname = trim(preg_replace('/\s+/', ' ', str_replace(['\t', '\n', '\r'], ' ', $pl['player']['name'] ?? '')));
                $ppid = $pl['player']['id'] ?? null;
                foreach ($pl['transfers'] ?? [] as $tr) {
                    $inId = $tr['teams']['in']['id'] ?? null;
                    $outId = $tr['teams']['out']['id'] ?? null;
                    $type = $tr['type'] ?? null;
                    if ($inId === $outId || $type === 'Raise') {
                        continue; // buang kenaikan kontrak / klub sama
                    }
                    $rows->push([
                        'date'    => $tr['date'] ?? '',
                        'player'  => $pname,
                        'pid'     => $ppid,
                        'inId'    => $inId,
                        'inName'  => $tr['teams']['in']['name'] ?? '',
                        'outId'   => $outId,
                        'outName' => $tr['teams']['out']['name'] ?? '',
                        'outLogo' => $tr['teams']['out']['logo'] ?? null,
                        'inLogo'  => $tr['teams']['in']['logo'] ?? null,
                        'type'    => $type,
                    ]);
                }
            }
            $aid = (int) $team->api_id;
            $dedup = fn ($c) => $c->sortByDesc('date')->unique(fn ($r) => $r['player'] . '|' . substr($r['date'], 0, 7))->take(20)->values();
            $transfersIn  = $dedup($rows->filter(fn ($r) => (int) $r['inId'] === $aid));
            $transfersOut = $dedup($rows->filter(fn ($r) => (int) $r['outId'] === $aid));
        }

        // Warna jersey tim (dari lineup laga terakhir) → header
        $teamColor = $lastXI['team']['colors']['player']['primary'] ?? null;
        $darkText = false;
        if ($teamColor && strlen($teamColor) === 6 && ctype_xdigit($teamColor)) {
            $lum = 0.299 * hexdec(substr($teamColor, 0, 2)) + 0.587 * hexdec(substr($teamColor, 2, 2)) + 0.114 * hexdec(substr($teamColor, 4, 2));
            $darkText = $lum > 150;
        } else {
            $teamColor = null;
        }
        


        return view('pages.team', compact('team', 'upcoming', 'recent', 'nextMatch', 'formMatches', 'standings', 'country', 'lastXI', 'xiStats', 'lastFixture', 'venue', 'founded', 'coach', 'topRated', 'topScorers', 'topAssists', 'seasonStats', 'squadGroups', 'playerLeaders', 'teamStats', 'transfersIn', 'transfersOut', 'teamColor', 'darkText'));
    }
}