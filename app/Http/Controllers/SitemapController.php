<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Fixture;
use App\Models\League;
use App\Models\Page;
use App\Models\Team;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index()
    {
        $xml = Cache::remember('sitemap.xml', 86400, function () {
            $lines = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];
            $add = function ($path, $priority, $lastmod = null) use (&$lines) {
                $loc = htmlspecialchars(\App\Models\Domain::to($path), ENT_XML1);
                $mod = $lastmod ? "<lastmod>{$lastmod}</lastmod>" : '';
                $lines[] = "  <url><loc>{$loc}</loc>{$mod}<priority>{$priority}</priority></url>";
            };

            // Halaman utama
            $add('/', '1.0');
            $add('/news', '0.9');
            $add('/leagues', '0.8');
            $add('/transfers', '0.6');
            $add('/tips', '0.7');
            $add('/highlights', '0.7');

            // Artikel (konten utama buat SEO)
            foreach (Article::published()->get(['slug', 'updated_at']) as $a) {
                $add('/news/' . $a->slug, '0.7', $a->updated_at->toDateString());
            }

            // Halaman statis
            foreach (Page::pluck('slug') as $slug) {
                $add('/page/' . $slug, '0.4');
            }

            // Liga, tim, match
            foreach (League::pluck('id') as $id) {
                $add('/league/' . $id, '0.8');
            }
            foreach (Team::pluck('id') as $id) {
                $add('/team/' . $id, '0.7');
            }
            foreach (Fixture::pluck('id') as $id) {
                $add('/match/' . $id, '0.5');
            }

            $lines[] = '</urlset>';

            return implode("\n", $lines);
        });

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}