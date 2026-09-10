<?php

namespace App\Http\Controllers;

use App\Models\Journey;
use Illuminate\View\View;

class JourneyController extends Controller
{
    public function index(): View
    {
        $journeys = Journey::query()
            ->withCount('steps')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->get();

        return view('journeys.index', compact('journeys'));
    }

    public function show(Journey $journey): View
    {
        $journey->load(['steps', 'category']);

        return view('journeys.show', compact('journey'));
    }

    public function step(Journey $journey, int $step): View
    {
        $journey->load('steps');

        $current = $journey->steps->firstWhere('step_number', $step);
        abort_unless($current, 404);

        $previous = $journey->steps->firstWhere('step_number', $step - 1);
        $next = $journey->steps->firstWhere('step_number', $step + 1);

        return view('journeys.step', compact('journey', 'current', 'previous', 'next'));
    }
}
