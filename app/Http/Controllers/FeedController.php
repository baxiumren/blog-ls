<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Setting;

class FeedController extends Controller
{
    public function index()
    {
        $articles = Article::published()->take(30)->get();
        $site = Setting::get('site_name') ?: 'LiveScore';

        return response()
            ->view('feed', compact('articles', 'site'))
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}