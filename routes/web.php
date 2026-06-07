<?php


use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\LeagueController as AdminLeagueController;
use App\Http\Controllers\Admin\PredictionController;
use App\Http\Controllers\Admin\SubscriberController;
use App\Http\Controllers\Admin\HighlightController;
use App\Http\Controllers\Admin\MatchOfDayController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Cache;
use App\Models\Fixture;
use App\Models\Team;
use App\Models\League;
use Illuminate\Http\Request;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\FixtureController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\NewsController;

Route::get('/', [HomeController::class, 'index']);

Route::get('/feed', [\App\Http\Controllers\FeedController::class, 'index']);

Route::get('/manifest.webmanifest', function () {
    $name = \App\Models\Setting::get('site_name') ?: 'LiveScore';
    return response()->json([
        'name'             => $name,
        'short_name'       => $name,
        'start_url'        => '/',
        'display'          => 'standalone',
        'background_color' => '#09090b',
        'theme_color'      => '#2563eb',
        'icons'            => [
            ['src' => '/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ['src' => '/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
        ],
    ])->header('Content-Type', 'application/manifest+json');
});

Route::view('/offline', 'offline');

Route::post('/subscribe', [\App\Http\Controllers\SubscribeController::class, 'store']);

Route::get('/match/{id}', [FixtureController::class, 'show']);
Route::post('/match/{fixture}/vote', [\App\Http\Controllers\PollController::class, 'vote']);

Route::get('/league/{id}', [LeagueController::class, 'show']);
Route::get('/leagues', [LeagueController::class, 'index']);
Route::get('/author/{user}', [\App\Http\Controllers\AuthorController::class, 'show']);
Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/{slug}', [NewsController::class, 'show']);
Route::post('/news/{slug}/comment', [\App\Http\Controllers\CommentController::class, 'store']);
Route::post('/news/{slug}/react', [\App\Http\Controllers\ReactionController::class, 'store']);
Route::get('/news/category/{category}', [NewsController::class, 'category']);
Route::get('/news/tag/{tag}', [NewsController::class, 'tag']);
Route::get('/tips', [\App\Http\Controllers\TipsController::class, 'index']);
Route::get('/highlights', function () {
    $highlights = \App\Models\Highlight::with(['fixture.homeTeam', 'fixture.awayTeam', 'fixture.league'])
        ->whereHas('fixture')
        ->latest()->paginate(12);
    return view('pages.highlights', compact('highlights'));
});

Route::get('/page/{slug}', [\App\Http\Controllers\PageController::class, 'show']);

Route::view('/transfers', 'pages.transfers');
Route::get('/team/{id}', [TeamController::class, 'show']);
Route::get('/player/{id}', [PlayerController::class, 'show']);
Route::get('/search', function (Request $request, \App\Services\ApiFootball $api) {
    $q = trim($request->query('q', ''));
    if (mb_strlen($q) < 2) {
        return response()->json(['teams' => [], 'leagues' => [], 'players' => [], 'matches' => [], 'articles' => []]);
    }
    $teams = Team::where('name', 'like', "%{$q}%")->orderBy('name')->limit(5)->get()
        ->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'logo' => $t->logo_url, 'url' => "/team/{$t->id}"]);
    $leagues = League::where('name', 'like', "%{$q}%")->orderBy('name')->limit(3)->get()
        ->map(fn ($l) => ['id' => $l->id, 'name' => $l->name, 'logo' => $l->logo_url, 'url' => "/league/{$l->id}"]);
    $matches = Fixture::with(['homeTeam', 'awayTeam'])
        ->where(fn ($w) => $w->whereHas('homeTeam', fn ($x) => $x->where('name', 'like', "%{$q}%"))
            ->orWhereHas('awayTeam', fn ($x) => $x->where('name', 'like', "%{$q}%")))
        ->orderByDesc('kickoff_at')->limit(4)->get()
        ->map(fn ($f) => [
            'id'   => $f->id,
            'name' => $f->homeTeam->short_name . ' vs ' . $f->awayTeam->short_name,
            'sub'  => $f->kickoff_at->format('d M Y'),
            'url'  => "/match/{$f->id}",
        ]);
    $players = [];
    if (mb_strlen($q) >= 3) {
        $raw = Cache::remember('search.players.' . strtolower($q), 3600, fn () => $api->searchPlayers($q));
        $players = collect($raw)->take(5)->map(fn ($x) => [
            'id'    => $x['player']['id'] ?? null,
            'name'  => $x['player']['name'] ?? '',
            'photo' => $x['player']['photo'] ?? null,
            'sub'   => $x['player']['nationality'] ?? null,
            'url'   => '/player/' . ($x['player']['id'] ?? ''),
        ])->filter(fn ($r) => $r['id'])->values();
    }
    $articles = \App\Models\Article::published()->where('title', 'like', "%{$q}%")->limit(4)->get()
    ->map(fn ($a) => [
        'id'   => $a->id,
        'name' => $a->title,
        'sub'  => $a->category,
        'logo' => $a->image ? asset('storage/' . $a->image) : null,
        'url'  => '/news/' . $a->slug,
    ]);
    return response()->json(['teams' => $teams, 'leagues' => $leagues, 'players' => $players, 'matches' => $matches, 'articles' => $articles]);
});
Route::get('/live', function (\App\Services\ApiFootball $api) {
    $data = \Illuminate\Support\Facades\Cache::remember('live.fixtures', 30, function () use ($api) {
        $out = [];
        foreach ($api->live() as $f) {
            $short = $f['fixture']['status']['short'] ?? '';
            $st = in_array($short, ['FT', 'AET', 'PEN']) ? 'finished'
                : (in_array($short, ['1H', '2H', 'HT', 'ET', 'BT', 'P', 'LIVE', 'SUSP', 'INT']) ? 'live' : 'scheduled');
            $out[$f['fixture']['id']] = [
                'st'    => $st,
                'short' => $short,
                'min'   => $f['fixture']['status']['elapsed'],
                'hs'    => $f['goals']['home'],
                'as'    => $f['goals']['away'],
            ];
        }
        return $out;
    });
    return response()->json($data);
});
Route::get('/robots.txt', function () {
    $robots = "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /install\n\nSitemap: " . \App\Models\Domain::to('/sitemap.xml') . "\n";

    return response($robots, 200)->header('Content-Type', 'text/plain');
});
Route::get('/unsubscribe/{token}', [\App\Http\Controllers\UnsubscribeController::class, 'show']);
Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login']);
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

Route::middleware('admin')->prefix('admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Content — admin & editor
    Route::get('/articles/{article}/preview', [ArticleController::class, 'preview'])->name('admin.articles.preview');
    Route::get('/articles', [ArticleController::class, 'index'])->name('admin.articles.index');
    Route::get('/articles/create', [ArticleController::class, 'create'])->name('admin.articles.create');
    Route::post('/articles', [ArticleController::class, 'store'])->name('admin.articles.store');
    Route::get('/articles/{article}/edit', [ArticleController::class, 'edit'])->name('admin.articles.edit');
    Route::put('/articles/{article}', [ArticleController::class, 'update'])->name('admin.articles.update');
    Route::delete('/articles/{article}', [ArticleController::class, 'destroy'])->name('admin.articles.destroy');

    Route::get('/pages', [PageController::class, 'index'])->name('admin.pages.index');
    Route::get('/pages/create', [PageController::class, 'create'])->name('admin.pages.create');
    Route::post('/pages', [PageController::class, 'store'])->name('admin.pages.store');
    Route::get('/pages/{page}/edit', [PageController::class, 'edit'])->name('admin.pages.edit');
    Route::put('/pages/{page}', [PageController::class, 'update'])->name('admin.pages.update');
    Route::delete('/pages/{page}', [PageController::class, 'destroy'])->name('admin.pages.destroy');

    Route::get('/predictions', [PredictionController::class, 'index'])->name('admin.predictions.index');
    Route::get('/predictions/create', [PredictionController::class, 'create'])->name('admin.predictions.create');
    Route::post('/predictions', [PredictionController::class, 'store'])->name('admin.predictions.store');
    Route::get('/predictions/{prediction}/edit', [PredictionController::class, 'edit'])->name('admin.predictions.edit');
    Route::put('/predictions/{prediction}', [PredictionController::class, 'update'])->name('admin.predictions.update');
    Route::delete('/predictions/{prediction}', [PredictionController::class, 'destroy'])->name('admin.predictions.destroy');

    Route::get('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('admin.profile');
    Route::put('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('admin.profile.update');

    Route::get('/highlights', [HighlightController::class, 'index'])->name('admin.highlights.index');
    Route::get('/highlights/create', [HighlightController::class, 'create'])->name('admin.highlights.create');
    Route::post('/highlights', [HighlightController::class, 'store'])->name('admin.highlights.store');
    Route::get('/highlights/{highlight}/edit', [HighlightController::class, 'edit'])->name('admin.highlights.edit');
    Route::put('/highlights/{highlight}', [HighlightController::class, 'update'])->name('admin.highlights.update');
    Route::delete('/highlights/{highlight}', [HighlightController::class, 'destroy'])->name('admin.highlights.destroy');

    Route::get('/motd', [MatchOfDayController::class, 'edit'])->name('admin.motd');
    Route::post('/motd', [MatchOfDayController::class, 'update'])->name('admin.motd.update');

    Route::get('/comments', [\App\Http\Controllers\Admin\CommentController::class, 'index'])->name('admin.comments.index');
    Route::post('/comments/{comment}/approve', [\App\Http\Controllers\Admin\CommentController::class, 'approve'])->name('admin.comments.approve');
    Route::delete('/comments/{comment}', [\App\Http\Controllers\Admin\CommentController::class, 'destroy'])->name('admin.comments.destroy');

    // Admin-only
    Route::middleware('role.admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create');
        Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

        Route::get('/leagues', [AdminLeagueController::class, 'index'])->name('admin.leagues.index');
        Route::post('/leagues', [AdminLeagueController::class, 'update'])->name('admin.leagues.update');

        Route::get('/subscribers', [SubscriberController::class, 'index'])->name('admin.subscribers.index');
        Route::get('/subscribers/export', [SubscriberController::class, 'export'])->name('admin.subscribers.export');
        Route::delete('/subscribers/{subscriber}', [SubscriberController::class, 'destroy'])->name('admin.subscribers.destroy');
        Route::get('/newsletter', [\App\Http\Controllers\Admin\NewsletterController::class, 'create'])->name('admin.newsletter.create');
        Route::post('/newsletter', [\App\Http\Controllers\Admin\NewsletterController::class, 'send'])->name('admin.newsletter.send');
        Route::get('/cron', [\App\Http\Controllers\Admin\CronController::class, 'index'])->name('admin.cron');
        Route::get('/cache', [\App\Http\Controllers\Admin\CacheController::class, 'index'])->name('admin.cache');
        Route::post('/cache/clear', [\App\Http\Controllers\Admin\CacheController::class, 'clear'])->name('admin.cache.clear');
        Route::get('/health', [\App\Http\Controllers\Admin\HealthController::class, 'index'])->name('admin.health');
        Route::get('/domains', [\App\Http\Controllers\Admin\DomainController::class, 'index'])->name('admin.domains');
        Route::post('/domains', [\App\Http\Controllers\Admin\DomainController::class, 'store'])->name('admin.domains.store');
        Route::post('/domains/{domain}/refresh', [\App\Http\Controllers\Admin\DomainController::class, 'refresh'])->name('admin.domains.refresh');
        Route::post('/domains/{domain}/primary', [\App\Http\Controllers\Admin\DomainController::class, 'primary'])->name('admin.domains.primary');
        Route::delete('/domains/{domain}', [\App\Http\Controllers\Admin\DomainController::class, 'destroy'])->name('admin.domains.destroy');
        Route::post('/domains/{domain}/redirect', [\App\Http\Controllers\Admin\DomainController::class, 'redirect'])->name('admin.domains.redirect');
        Route::get('/logs', [\App\Http\Controllers\Admin\LogController::class, 'index'])->name('admin.logs');
        Route::post('/logs/clear', [\App\Http\Controllers\Admin\LogController::class, 'clear'])->name('admin.logs.clear');

        Route::get('/settings/{group?}', [SettingController::class, 'show'])->name('admin.settings');
        Route::post('/settings/{group}', [SettingController::class, 'update'])->name('admin.settings.update');
    });
});
