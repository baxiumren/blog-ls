<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    public function index()
    {
        $xml = Cache::get('sitemap.xml');

        return view('admin.seo', [
            'urlCount'     => $xml ? substr_count($xml, '<url>') : null,
            'cached'       => (bool) $xml,
            'robotsCustom' => Setting::get('robots_custom'),
            'base'         => Domain::activeBaseUrl(),
        ]);
    }

    public function saveRobots(Request $request)
    {
        Setting::put('robots_custom', trim((string) $request->input('robots_custom')));

        return back()->with('ok', 'Robots rules saved.');
    }

    public function clearSitemap()
    {
        Cache::forget('sitemap.xml');

        return back()->with('ok', 'Sitemap cache cleared — it will regenerate on the next visit.');
    }
}
