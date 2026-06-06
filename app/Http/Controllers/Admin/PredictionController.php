<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fixture;
use App\Models\Prediction;
use Illuminate\Http\Request;

class PredictionController extends Controller
{
    public function index()
    {
        $predictions = Prediction::with('fixture.homeTeam', 'fixture.awayTeam')->latest()->paginate(15);
        return view('admin.predictions.index', compact('predictions'));
    }

    public function create()
    {
        return view('admin.predictions.form', ['prediction' => new Prediction(), 'fixtures' => $this->fixtureOptions()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['user_id'] = auth()->id();
        $data['published_at'] = $request->boolean('published') ? now() : null;
        Prediction::create($data);
        return redirect('/admin/predictions')->with('ok', 'Prediction created.');
    }

    public function edit(Prediction $prediction)
    {
        return view('admin.predictions.form', ['prediction' => $prediction, 'fixtures' => $this->fixtureOptions($prediction->fixture_id)]);
    }

    public function update(Request $request, Prediction $prediction)
    {
        $data = $this->validated($request, $prediction->id);
        $data['published_at'] = $request->boolean('published') ? ($prediction->published_at ?? now()) : null;
        $prediction->update($data);
        return redirect('/admin/predictions')->with('ok', 'Prediction updated.');
    }

    public function destroy(Prediction $prediction)
    {
        $prediction->delete();
        return redirect('/admin/predictions')->with('ok', 'Prediction deleted.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'fixture_id'      => ['required', 'exists:fixtures,id', 'unique:predictions,fixture_id' . ($ignoreId ? ',' . $ignoreId : '')],
            'tip'             => ['required', 'string', 'max:100'],
            'predicted_score' => ['nullable', 'string', 'max:10'],
            'confidence'      => ['required', 'integer', 'min:1', 'max:5'],
            'body'            => ['nullable', 'string'],
        ]);
    }

    private function fixtureOptions($includeId = null)
    {
        return Fixture::with('homeTeam', 'awayTeam')
            ->where(function ($q) use ($includeId) {
                $q->where('kickoff_at', '>=', now()->subDay())->where('status', 'scheduled');
                if ($includeId) $q->orWhere('id', $includeId);
            })
            ->orderBy('kickoff_at')
            ->take(300)
            ->get();
    }
}