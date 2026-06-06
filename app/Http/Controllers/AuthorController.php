<?php

namespace App\Http\Controllers;

use App\Models\User;

class AuthorController extends Controller
{
    public function show(User $user)
    {
        $articles = $user->articles()->published()->paginate(9);
        return view('pages.author', compact('user', 'articles'));
    }
}