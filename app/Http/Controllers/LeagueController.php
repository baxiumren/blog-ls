<?php

namespace App\Http\Controllers;

use App\Models\League;
use App\Models\Team;
use App\Services\ApiFootball;
use Illuminate\Support\Facades\Cache;

class LeagueController extends Controller
{
    public function show($id, ApiFootball $api)
    {
        $league = League::findOrFail($id);

        $season = $league->season ?? 2025;

        // Daftar musim (buat selector) + override dari ?season=
        $availableSeasons = collect();
        if ($league->api_id) {
            $info = Cache::remember("league.{$league->api_id}.info", 604800, fn () => $api->league($league->api_id));
            $availableSeasons = collect($info['seasons'] ?? [])->pluck('year')->filter()->sortDesc()->values();
        }
        $reqSeason = (int) request('season', 0);
        if ($availableSeasons->contains($reqSeason)) {
            $season = $reqSeason;
        }

        $upcoming = $league->fixtures()->where('kickoff_at', '>=', now())->with(['homeTeam', 'awayTeam'])->orderBy('kickoff_at')->limit(30)->get();
        $recent = $league->fixtures()->where('status', 'finished')->with(['homeTeam', 'awayTeam'])->orderByDesc('kickoff_at')->limit(30)->get();

        // Standings dari API (dengan grup)
        $standingsGroups = [];
        $teamIdMap = collect();
        if ($league->api_id) {
            $apiStandings = Cache::remember("league.{$league->api_id}.standings.{$season}", 600, fn () => $api->standings($league->api_id, $season));
            $apiIds = [];
            foreach ($apiStandings as $group) {
                if (empty($group)) {
                    continue;
                }
                $groupName = $group[0]['group'] ?? 'Table';
                $standingsGroups[$groupName] = collect($group)->map(function ($r) use (&$apiIds) {
                    $apiIds[] = $r['team']['id'] ?? null;
                    return [
                        'rank' => $r['rank'] ?? null,
                        'team' => $r['team']['name'] ?? '', 'teamApiId' => $r['team']['id'] ?? null, 'logo' => $r['team']['logo'] ?? null,
                        'played' => $r['all']['played'] ?? 0, 'win' => $r['all']['win'] ?? 0, 'draw' => $r['all']['draw'] ?? 0, 'lose' => $r['all']['lose'] ?? 0,
                        'gf' => $r['all']['goals']['for'] ?? 0, 'ga' => $r['all']['goals']['against'] ?? 0,
                        'gd' => $r['goalsDiff'] ?? 0, 'points' => $r['points'] ?? 0, 'form' => $r['form'] ?? null,
                        'form' => $r['form'] ?? null,
                        'description' => $r['description'] ?? null,
                    ];
                })->all();
            }
            $teamIdMap = Team::whereIn('api_id', array_filter($apiIds))->pluck('id', 'api_id');
        }

        // Team stats liga — dihitung dari standings (gratis)
        $leagueTeamStats = [];
        $allRows = collect($standingsGroups)->flatMap(fn ($rows) => $rows);
        if ($allRows->isNotEmpty() && $allRows->sum('played') > 0) {
            $mk = fn ($key, $dir) => ($dir === 'desc' ? $allRows->sortByDesc($key) : $allRows->sortBy($key))
                ->take(5)->map(fn ($r) => ['team' => $r['team'], 'logo' => $r['logo'], 'teamApiId' => $r['teamApiId'], 'value' => $r[$key]])->values();
            $leagueTeamStats = [
                ['title' => 'Most goals', 'rows' => $mk('gf', 'desc')],
                ['title' => 'Best defense', 'rows' => $mk('ga', 'asc')],
                ['title' => 'Most wins', 'rows' => $mk('win', 'desc')],
                ['title' => 'Goal difference', 'rows' => $mk('gd', 'desc')],
            ];
        }

        // Player stats
        $topScorers = collect();
        $topAssists = collect();
        $topYellow = collect();
        if ($league->api_id) {
            $topScorers = collect(Cache::remember("league.{$league->api_id}.topscorers.{$season}", 86400, fn () => $api->topScorers($league->api_id, $season)))
                ->take(10)->map(fn ($x) => [
                    'name' => $x['player']['name'] ?? '', 'photo' => $x['player']['photo'] ?? null, 'id' => $x['player']['id'] ?? null,
                    'team' => $x['statistics'][0]['team']['name'] ?? '', 'teamLogo' => $x['statistics'][0]['team']['logo'] ?? null,
                    'value' => $x['statistics'][0]['goals']['total'] ?? 0,
                ])->values();
            $topAssists = collect(Cache::remember("league.{$league->api_id}.topassists.{$season}", 86400, fn () => $api->topAssists($league->api_id, $season)))
                ->take(10)->map(fn ($x) => [
                    'name' => $x['player']['name'] ?? '', 'photo' => $x['player']['photo'] ?? null, 'id' => $x['player']['id'] ?? null,
                    'team' => $x['statistics'][0]['team']['name'] ?? '', 'teamLogo' => $x['statistics'][0]['team']['logo'] ?? null,
                    'value' => $x['statistics'][0]['goals']['assists'] ?? 0,
                ])->values();
            $topYellow = collect(Cache::remember("league.{$league->api_id}.topyellow.{$season}", 86400, fn () => $api->topYellowCards($league->api_id, $season)))
            ->take(10)->map(fn ($x) => [
                'name' => $x['player']['name'] ?? '', 'photo' => $x['player']['photo'] ?? null, 'id' => $x['player']['id'] ?? null,
                'team' => $x['statistics'][0]['team']['name'] ?? '', 'teamLogo' => $x['statistics'][0]['team']['logo'] ?? null,
                'value' => $x['statistics'][0]['cards']['yellow'] ?? 0,
            ])->values();
        }

        $country = $league->country ?? $league->name;
        // Rounds + fixtures per-round (buat selector di tab Fixtures)
        $rounds = $league->fixtures()->whereNotNull('round')
        ->selectRaw('round, MIN(kickoff_at) as first_kick')
        ->groupBy('round')->orderBy('first_kick')->pluck('round');
        $selectedRound = request('round');
        if (! $rounds->contains($selectedRound)) {
        $nextFix = $league->fixtures()->where('kickoff_at', '>=', now())->orderBy('kickoff_at')->first();
        $selectedRound = $nextFix->round ?? $rounds->last();
        }
        $roundFixtures = $selectedRound
        ? $league->fixtures()->with(['homeTeam', 'awayTeam'])->where('round', $selectedRound)->orderBy('kickoff_at')->get()
        : collect();

        $rIdx = $rounds->search($selectedRound);
        $prevRound = ($rIdx !== false && $rIdx > 0) ? $rounds[$rIdx - 1] : null;
        $nextRound = ($rIdx !== false && $rIdx < $rounds->count() - 1) ? $rounds[$rIdx + 1] : null;

        // Knockout bracket (cup dengan babak gugur)
        $koOrder = ['Round of 32', 'Round of 16', 'Quarter-finals', 'Semi-finals', '3rd Place Final', 'Final'];
        $koRounds = [];
        foreach ($koOrder as $rn) {
            $fx = $league->fixtures()->with(['homeTeam', 'awayTeam'])->where('round', $rn)->orderBy('kickoff_at')->get();
            if ($fx->isNotEmpty()) {
                $koRounds[$rn] = $fx;
            }
        }

        return view('pages.league', compact('league', 'upcoming', 'recent', 'standingsGroups', 'teamIdMap', 'topScorers', 'topAssists', 'country', 'season', 'leagueTeamStats', 'availableSeasons', 'rounds', 'selectedRound', 'roundFixtures', 'koRounds', 'topYellow', 'prevRound', 'nextRound'));
    }

    public function index()
    {
        $leagues = League::orderBy('id')->get();
        return view('pages.leagues', compact('leagues'));
    }
}