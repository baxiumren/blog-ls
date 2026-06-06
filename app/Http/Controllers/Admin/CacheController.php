<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class CacheController extends Controller
{
    public function index()
    {
        return view('admin.cache', [
            'driver' => config('cache.default'),
            'items'  => [
                'Site settings'        => 'settings',
                'Sitemap'              => 'sitemap.xml',
                'Footer leagues'       => 'footer.leagues',
                'Live scores'          => 'live.fixtures',
                'Compiled views'       => 'view:clear',
                'Config & routes'      => 'config:clear / route:clear',
            ],
        ]);
    }

    public function clear()
    {
        Artisan::call('cache:clear');   // flush app data cache (settings, sitemap, etc.)
        Artisan::call('view:clear');    // compiled Blade templates
        Artisan::call('config:clear');  // config cache
        Artisan::call('route:clear');   // route cache

        return back()->with('ok', 'All caches cleared successfully.');
    }
}