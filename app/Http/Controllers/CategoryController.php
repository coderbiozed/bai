<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Journey;
use App\Models\Prompt;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->withCount(['subcategories', 'prompts'])
            ->orderBy('sort_order')
            ->get();

        $featuredJourneys = Journey::query()
            ->withCount('steps')
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->get();

        $bestPrompts = Prompt::query()
            ->published()
            ->where('is_best', true)
            ->with(['subcategory.category'])
            ->latest()
            ->take(4)
            ->get();

        $instantPrompts = Prompt::query()
            ->published()
            ->whereHas('subcategory', fn ($q) => $q->where('slug', 'quick-test')
                ->whereHas('category', fn ($c) => $c->where('slug', 'instant-solutions')))
            ->with(['subcategory.category'])
            ->orderByDesc('is_best')
            ->orderBy('id')
            ->get();

        $stats = [
            'prompts' => Prompt::query()->published()->count(),
            'verified' => Prompt::query()->published()->verified()->count(),
            'journeys' => Journey::query()->count(),
        ];

        return view('home', compact('categories', 'bestPrompts', 'featuredJourneys', 'instantPrompts', 'stats'));
    }

    public function show(Category $category): View
    {
        $category->load(['subcategories' => fn ($q) => $q->withCount('prompts')]);

        return view('categories.show', compact('category'));
    }
}
