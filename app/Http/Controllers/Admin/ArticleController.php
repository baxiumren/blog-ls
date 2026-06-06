<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\League;
use App\Models\Team;
use App\Models\PageView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::with('league')->latest();

        if ($s = trim((string) $request->query('q'))) {
            $query->where('title', 'like', "%{$s}%");
        }
        if ($request->query('status') === 'published') {
            $query->whereNotNull('published_at');
        } elseif ($request->query('status') === 'draft') {
            $query->whereNull('published_at');
        }

        $articles = $query->paginate(12)->withQueryString();

        $views = PageView::whereIn('path', $articles->getCollection()->map(fn ($a) => '/news/' . $a->slug)->all())
            ->selectRaw('path, count(*) as c')->groupBy('path')->pluck('c', 'path');
        $articles->getCollection()->each(fn ($a) => $a->setAttribute('views', (int) ($views['/news/' . $a->slug] ?? 0)));

        return view('admin.articles.index', compact('articles'));
    }

    public function create()
    {
        return view('admin.articles.form', $this->formData(new Article()));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['user_id'] = auth()->id();
        $data['slug'] = $this->uniqueSlug($request->input('slug') ?: $data['title']);
        $data['published_at'] = $this->resolvePublishedAt($request, null);
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('articles', 'public');
        }
        $data['is_featured'] = $request->boolean('is_featured');
        $data['noindex'] = $request->boolean('noindex');
        Article::create($data);
        return redirect('/admin/articles')->with('ok', 'Article created.');
    }

    public function edit(Article $article)
    {
        return view('admin.articles.form', $this->formData($article));
    }

    public function update(Request $request, Article $article)
    {
        $data = $this->validated($request);
        $data['slug'] = $request->filled('slug') ? $this->uniqueSlug($request->input('slug'), $article->id) : $article->slug;
        $data['published_at'] = $this->resolvePublishedAt($request, $article);

        if ($request->boolean('remove_image') && $article->image) {
            Storage::disk('public')->delete($article->image);
            $data['image'] = null;
        }
        if ($request->hasFile('image')) {
            if ($article->image) {
                Storage::disk('public')->delete($article->image);
            }
            $data['image'] = $request->file('image')->store('articles', 'public');
        }
        $data['is_featured'] = $request->boolean('is_featured');
        $data['noindex'] = $request->boolean('noindex');
        $article->update($data);
        return redirect('/admin/articles')->with('ok', 'Article updated.');
    }

    public function destroy(Article $article)
    {
        if ($article->image) {
            Storage::disk('public')->delete($article->image);
        }
        $article->delete();
        return redirect('/admin/articles')->with('ok', 'Article deleted.');
    }

    public function preview(Article $article)
    {
        $related = Article::published()->where('id', '!=', $article->id)->take(4)->get();
        return view('pages.article', compact('article', 'related'));
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:255'],
            'excerpt'          => ['nullable', 'string', 'max:500'],
            'body'             => ['required', 'string'],
            'category'         => ['required', 'string', 'max:50'],
            'tags'             => ['nullable', 'string', 'max:255'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'is_featured'      => ['nullable', 'boolean'],
            'publish_at'       => ['nullable', 'date'],
            'league_id'        => ['nullable', 'exists:leagues,id'],
            'team_id'          => ['nullable', 'exists:teams,id'],
            'image'            => ['nullable', 'image', 'max:4096'],
        ]);
        unset($data['image'], $data['publish_at']);
        return $data;
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = \Illuminate\Support\Str::slug($value) ?: 'article';
        $slug = $base;
        $i = 1;
        while (Article::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    private function formData(Article $article): array
    {
        return [
            'article' => $article,
            'leagues' => League::orderBy('name')->get(['id', 'name']),
            'teams'   => Team::orderBy('name')->get(['id', 'name']),
        ];
    }

    private function resolvePublishedAt(Request $request, ?Article $article)
    {
        if ($request->filled('publish_at')) {
            return \Carbon\Carbon::parse($request->input('publish_at'));
        }
        if ($request->boolean('published')) {
            return $article && $article->published_at ? $article->published_at : now();
        }
        return null;
    }
}