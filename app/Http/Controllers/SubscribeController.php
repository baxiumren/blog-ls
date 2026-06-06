<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\Request;

class SubscribeController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255']]);
        Subscriber::firstOrCreate(
            ['email' => $data['email']],
            ['ip' => $request->ip(), 'token' => \Illuminate\Support\Str::random(40)]
        );
        return response()->json(['ok' => true, 'message' => 'Thanks for subscribing!']);
    }
}