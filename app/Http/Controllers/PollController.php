<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\MatchVote;
use Illuminate\Http\Request;

class PollController extends Controller
{
    public function vote(Request $request, Fixture $fixture)
    {
        $data = $request->validate(['choice' => ['required', 'in:home,draw,away']]);
        $visitor = substr(hash('sha256', $request->ip() . $request->userAgent()), 0, 40);

        MatchVote::updateOrCreate(
            ['fixture_id' => $fixture->id, 'visitor' => $visitor],
            ['choice' => $data['choice']]
        );

        return response()->json(MatchVote::tally($fixture->id));
    }
}