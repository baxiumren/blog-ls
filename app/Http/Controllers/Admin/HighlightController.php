<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fixture;
use App\Models\Highlight;
use Illuminate\Http\Request;

class HighlightController extends Controller
{
    public function index()
    {
        $highlights = Highlight::with('fixture.homeTeam', 'fixture.awayTeam')->latest()->paginate(15);
        return view('admin.highlights.index', compact('highlights'));
    }

    public function create()
    {
        return view('admin.highlights.form', ['highlight' => new Highlight(), 'fixtures' => $this->fixtureOptions()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['user_id'] = auth()->id();
        Highlight::create($data);
        return redirect('/admin/highlights')->with('ok', 'Highlight added.');
    }

    public function edit(Highlight $highlight)
    {
        return view('admin.highlights.form', ['highlight' => $highlight, 'fixtures' => $this->fixtureOptions($highlight->fixture_id)]);
    }

    public function update(Request $request, Highlight $highlight)
    {
        $highlight->update($this->validated($request, $highlight->id));
        return redirect('/admin/highlights')->with('ok', 'Highlight updated.');
    }

    public function destroy(Highlight $highlight)
    {
        $highlight->delete();
        return redirect('/admin/highlights')->with('ok', 'Highlight deleted.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'fixture_id'  => ['required', 'exists:fixtures,id', 'unique:highlights,fixture_id' . ($ignoreId ? ',' . $ignoreId : '')],
            'youtube_url' => ['required', 'url', 'max:255'],
            'title'       => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function fixtureOptions($includeId = null)
    {
        return Fixture::with('homeTeam', 'awayTeam')
            ->where(function ($q) use ($includeId) {
                $q->where('status', 'finished')->where('kickoff_at', '>=', now()->subDays(60));
                if ($includeId) {
                    $q->orWhere('id', $includeId);
                }
            })
            ->orderByDesc('kickoff_at')->take(500)->get();
    }
}