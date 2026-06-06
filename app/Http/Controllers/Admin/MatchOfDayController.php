<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fixture;
use App\Models\Setting;
use Illuminate\Http\Request;

class MatchOfDayController extends Controller
{
    public function edit()
    {
        $fixtures = Fixture::with('homeTeam', 'awayTeam')
            ->where('status', 'scheduled')->where('kickoff_at', '>=', now()->subHours(3))
            ->orderBy('kickoff_at')->take(300)->get();
        $current = (int) (Setting::get('motd_fixture_id') ?: 0);
        return view('admin.motd', compact('fixtures', 'current'));
    }

    public function update(Request $request)
    {
        Setting::put('motd_fixture_id', $request->input('fixture_id') ?: null);
        return back()->with('ok', $request->input('fixture_id') ? 'Match of the Day set.' : 'Match of the Day cleared.');
    }
}