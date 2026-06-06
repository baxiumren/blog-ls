<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Reaction;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    public array $allowed = ['👍', '❤️', '🔥', '😮', '😂'];

    public function store(Request $request, $slug)
    {
        $article = Article::published()->where('slug', $slug)->firstOrFail();
        $emoji = (string) $request->input('emoji');
        abort_unless(in_array($emoji, $this->allowed, true), 422);

        $r = Reaction::firstOrCreate(['article_id' => $article->id, 'emoji' => $emoji]);
        $r->increment('count');

        return response()->json(
            Reaction::where('article_id', $article->id)->pluck('count', 'emoji')
        );
    }
}