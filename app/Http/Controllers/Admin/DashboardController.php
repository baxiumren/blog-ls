<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\League;
use App\Models\Page;
use App\Models\PageView;
use App\Models\Team;

class DashboardController extends Controller
{
    public function index()
    {
        $totalViews = PageView::count();
        $viewsToday = PageView::whereDate('created_at', today())->count();
        $viewsYesterday = PageView::whereDate('created_at', today()->subDay())->count();
        $uniqueToday = PageView::whereDate('created_at', today())->distinct()->count('visitor');

        // 7-day series
        $byDay = PageView::whereDate('created_at', '>=', today()->subDays(6))
            ->selectRaw('date(created_at) d, count(*) c')->groupBy('d')->pluck('c', 'd');
        $days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $days->push(['label' => $date->format('D'), 'count' => (int) ($byDay[$date->toDateString()] ?? 0)]);
        }
        $weekTotal = $days->sum('count');

        // Trends (vs previous period)
        $prevWeekTotal = PageView::whereDate('created_at', '>=', today()->subDays(13))
            ->whereDate('created_at', '<=', today()->subDays(7))->count();
        $weekTrend = $prevWeekTotal > 0 ? (int) round(($weekTotal - $prevWeekTotal) / $prevWeekTotal * 100) : null;
        $todayTrend = $viewsYesterday > 0 ? (int) round(($viewsToday - $viewsYesterday) / $viewsYesterday * 100) : null;

        // Traffic sources (referrer → host)
        $referrers = PageView::whereNotNull('referrer')->where('referrer', '!=', '')
            ->pluck('referrer')
            ->map(function ($r) {
                $host = parse_url($r, PHP_URL_HOST) ?: $r;
                return str_starts_with($host, 'www.') ? substr($host, 4) : $host;
            })
            ->countBy()->sortDesc()->take(6);

        // Most-read articles
        $topArticleRows = PageView::where('path', 'like', '/news/%')
            ->selectRaw('path, count(*) c')->groupBy('path')->orderByDesc('c')->take(5)->get();
        $artBySlug = Article::whereIn('slug', $topArticleRows->map(fn ($r) => str_replace('/news/', '', $r->path)))
            ->get()->keyBy('slug');
        $topArticles = $topArticleRows->map(function ($r) use ($artBySlug) {
            $a = $artBySlug->get(str_replace('/news/', '', $r->path));
            return $a ? ['article' => $a, 'views' => (int) $r->c] : null;
        })->filter()->values();

        // Content library
        $totalArticles = Article::count();
        $publishedArticles = Article::published()->count();
        $library = [
            ['Articles', $totalArticles, '/admin/articles'],
            ['Drafts', Article::whereNull('published_at')->count(), '/admin/articles?status=draft'],
            ['Pages', Page::count(), '/admin/pages'],
            ['Leagues', League::count(), '/admin/leagues'],
            ['Teams', Team::count(), null],
        ];

        // Recent articles + views
        $recentArticles = Article::latest()->take(8)->get();
        $viewsByPath = PageView::whereIn('path', $recentArticles->map(fn ($a) => '/news/' . $a->slug)->all())
            ->selectRaw('path, count(*) as c')->groupBy('path')->pluck('c', 'path');
        $recentArticles->each(fn ($a) => $a->setAttribute('views', (int) ($viewsByPath['/news/' . $a->slug] ?? 0)));
        $apiStatus = \Illuminate\Support\Facades\Cache::remember('api.status', 600, function () {
            try {
                return app(\App\Services\ApiFootball::class)->status();
            } catch (\Throwable $e) {
                return null;
            }
        });

        return view('admin.dashboard', compact(
            'totalViews', 'viewsToday', 'uniqueToday', 'days', 'weekTotal',
            'weekTrend', 'todayTrend', 'referrers', 'topArticles', 'library',
            'totalArticles', 'publishedArticles', 'recentArticles', 'apiStatus'
        ));
    }
}