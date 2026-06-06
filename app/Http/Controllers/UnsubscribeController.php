<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;

class UnsubscribeController extends Controller
{
    public function show($token)
    {
        $sub = Subscriber::where('token', $token)->first();
        if ($sub) {
            $sub->delete();
        }
        return view('pages.unsubscribe');
    }
}