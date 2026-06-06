<?php

namespace App\Http\Controllers;

use App\Models\Prediction;

class TipsController extends Controller
{
    public function index()
    {
        $tips = Prediction::published()
            ->join('fixtures', 'fixtures.id', '=', 'predictions.fixture_id')
            ->orderByDesc('fixtures.kickoff_at')
            ->with(['fixture.homeTeam', 'fixture.awayTeam', 'fixture.league'])
            ->select('predictions.*')
            ->paginate(12);

        return view('pages.tips', compact('tips'));
    }
}