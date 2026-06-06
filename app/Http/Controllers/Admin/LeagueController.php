<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\League;
use Illuminate\Http\Request;

class LeagueController extends Controller
{
    public function index()
    {
        $leagues = League::orderBy('priority')->orderBy('name')->get();
        return view('admin.leagues.index', compact('leagues'));
    }

    public function update(Request $request)
    {
        foreach ($request->input('priority', []) as $id => $pri) {
            League::where('id', $id)->update(['priority' => (int) $pri]);
        }
        return redirect('/admin/leagues')->with('ok', 'League order updated.');
    }
}