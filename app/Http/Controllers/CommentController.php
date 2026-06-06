<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, $slug)
    {
        $article = Article::published()->where('slug', $slug)->firstOrFail();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'body' => ['required', 'string', 'max:1000'],
        ]);
        Comment::create([
            'article_id' => $article->id,
            'name'       => $data['name'],
            'body'       => $data['body'],
            'approved'   => false,
            'ip'         => $request->ip(),
        ]);
        return back()->with('comment_ok', 'Thanks! Your comment is awaiting moderation.')->withFragment('comments');
    }
}