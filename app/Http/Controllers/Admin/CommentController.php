<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;

class CommentController extends Controller
{
    public function index()
    {
        $comments = Comment::with('article')->orderBy('approved')->latest()->paginate(20);
        $pending = Comment::where('approved', false)->count();
        return view('admin.comments.index', compact('comments', 'pending'));
    }

    public function approve(Comment $comment)
    {
        $comment->update(['approved' => ! $comment->approved]);
        return back()->with('ok', $comment->approved ? 'Comment approved.' : 'Comment hidden.');
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();
        return back()->with('ok', 'Comment deleted.');
    }
}