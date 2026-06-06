<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\League;
use App\Models\Fixture;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Ambil tanggal dari URL (?date=...), default hari ini
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : $this->defaultDate();

        $day = $date->toDateString();

        $leagues = Cache::remember("home.{$day}", 60, function () use ($day) {
            return League::whereHas('fixtures', fn ($q) => $q->whereDate('kickoff_at', $day))
                ->with(['fixtures' => function ($query) use ($day) {
                    $query->whereDate('kickoff_at', $day)
                        ->with(['homeTeam', 'awayTeam'])
                        ->orderBy('kickoff_at');
                }])
                ->orderBy('id')
                ->get();
        });
        
        // Urutan prioritas dari admin (kolom priority); Friendlies paling akhir
        $leagues = $leagues->sortBy(fn ($l) => $l->code === 'INT' ? 999 : ($l->priority ?? 100))->values();

        // --- Filter status: All / Live / Finished / Upcoming ---
        $filter = $request->query('filter', 'all');
        $statusMap = ['live' => 'live', 'finished' => 'finished', 'upcoming' => 'scheduled'];

        if (isset($statusMap[$filter])) {
            $want = $statusMap[$filter];
            $leagues = $leagues
                ->map(function ($league) use ($want) {
                    $league->setRelation('fixtures', $league->fixtures->where('status', $want)->values());
                    return $league;
                })
                ->filter(fn ($l) => $l->fixtures->isNotEmpty())
                ->values();
        }

        // --- Batasi 10 match, sisanya di balik tombol "Show all" ---
        $limit = 10;
        $showAll = $request->boolean('all');
        $totalMatches = $leagues->sum(fn ($l) => $l->fixtures->count());

        if (! $showAll && $totalMatches > $limit) {
            $remaining = $limit;
            $leagues = $leagues
                ->map(function ($league) use (&$remaining) {
                    $take = $league->fixtures->take(max($remaining, 0));
                    $remaining -= $take->count();
                    $league->setRelation('fixtures', $take);
                    return $league;
                })
                ->filter(fn ($l) => $l->fixtures->isNotEmpty())
                ->values();
        }

        $news = Article::published()->take(6)->get();

        $motdId = (int) (\App\Models\Setting::get('motd_fixture_id') ?: 0);
        $motd = $motdId ? Fixture::with(['homeTeam', 'awayTeam', 'league'])->find($motdId) : null;

        return view('pages.home', [
            'news'         => $news,
            'leagues'      => $leagues,
            'selectedDate' => $date,
            'totalMatches' => $totalMatches,
            'showAll'      => $showAll,
            'limit'        => $limit,
            'filter'       => $filter,
            'motd'         => $motd,
        ]);
    }

    // Cari tanggal default terbaik: match terdekat ke depan,
    // kalau gak ada (musim usai) → match terakhir yang udah lewat.
    private function defaultDate(): Carbon
    {
        $today = now()->toDateString();

        $upcoming = Fixture::whereDate('kickoff_at', '>=', $today)
            ->orderBy('kickoff_at')
            ->value('kickoff_at');
        if ($upcoming) {
            return Carbon::parse($upcoming);
        }

        $last = Fixture::orderByDesc('kickoff_at')->value('kickoff_at');
        if ($last) {
            return Carbon::parse($last);
        }

        return now(); // DB kosong
    }
}