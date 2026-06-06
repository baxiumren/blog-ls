<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Models\Article;
use App\Models\Setting;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) (Setting::get('articles_per_page') ?: 9);
        $category = $request->query('category');
        $search = trim((string) $request->query('q'));

        $featured = ($category || $search) ? collect() : Article::published()->featured()->take(5)->get();
        $featuredIds = $featured->pluck('id');

        $query = Article::published()->with('league')->whereNotIn('id', $featuredIds);
        if ($category) {
            $query->where('category', $category);
        }
        if ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        }
        $articles = $query->paginate($perPage)->withQueryString();

        $categories = Article::published()->select('category')->distinct()->orderBy('category')->pluck('category')->filter()->values();

        $trending = collect();
        if (! $search && ! $category) {
            $rows = \App\Models\PageView::where('created_at', '>=', now()->subDays(7))
                ->where('path', 'like', '/news/%')
                ->selectRaw('path, count(*) as c')->groupBy('path')->orderByDesc('c')->take(5)->get();
            $bySlug = Article::published()->whereIn('slug', $rows->map(fn ($r) => str_replace('/news/', '', $r->path)))->get()->keyBy('slug');
            $trending = $rows->map(fn ($r) => ($a = $bySlug->get(str_replace('/news/', '', $r->path))) ? ['article' => $a, 'views' => (int) $r->c] : null)->filter()->values();
        }

        return view('pages.news', compact('featured', 'articles', 'categories', 'category', 'trending'));
    }

    public function category($category)
    {
        $perPage = (int) (Setting::get('articles_per_page') ?: 9);
        $categories = Article::published()->select('category')->distinct()->orderBy('category')->pluck('category')->filter()->values();
        $matched = $categories->first(fn ($c) => Str::slug($c) === $category);
        abort_unless($matched, 404);

        $featured = collect();
        $articles = Article::published()->with('league')->where('category', $matched)->paginate($perPage)->withQueryString();
        $category = $matched;

        return view('pages.news', compact('featured', 'articles', 'categories', 'category'));
    }

    public function show($slug)
    {
        $article = Article::published()->with(['league', 'user'])->where('slug', $slug)->firstOrFail();

        $related = Article::published()->where('id', '!=', $article->id)
            ->where(function ($q) use ($article) {
                $q->where('category', $article->category);
                if ($article->team_id) $q->orWhere('team_id', $article->team_id);
                if ($article->league_id) $q->orWhere('league_id', $article->league_id);
            })
            ->take(4)->get();

        if ($related->count() < 4) {
            $fill = Article::published()->where('id', '!=', $article->id)
                ->whereNotIn('id', $related->pluck('id')->push($article->id)->all())
                ->take(4 - $related->count())->get();
            $related = $related->concat($fill);
        }
        $views = \App\Models\PageView::where('path', '/news/' . $article->slug)->count();

        $comments = $article->comments()->approved()->latest()->get();
        $reactions = \App\Models\Reaction::where('article_id', $article->id)->pluck('count', 'emoji');

        return view('pages.article', compact('article', 'related', 'views', 'comments', 'reactions'));
    }

    public function tag($tag)
    {
        $perPage = (int) (Setting::get('articles_per_page') ?: 9);
        $featured = collect();
        $articles = Article::published()->with('league')
            ->where('tags', 'like', '%' . $tag . '%')
            ->paginate($perPage)->withQueryString();
        $categories = Article::published()->select('category')->distinct()->orderBy('category')->pluck('category')->filter()->values();
        $category = '#' . $tag;

        return view('pages.news', compact('featured', 'articles', 'categories', 'category'));
    }
}