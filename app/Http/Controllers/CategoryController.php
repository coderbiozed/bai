<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Journey;
use App\Models\Prompt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $payload = Cache::remember('home.payload.v2', 180, function () {
            $categories = Category::query()
                ->withCount(['subcategories', 'prompts'])
                ->orderBy('sort_order')
                ->get(['id', 'name', 'slug', 'description', 'accent', 'sort_order']);

            $featuredJourneys = Journey::query()
                ->withCount('steps')
                ->where('is_featured', true)
                ->orderBy('sort_order')
                ->get(['id', 'title', 'slug', 'tagline', 'sort_order', 'is_featured']);

            $bestPrompts = Prompt::query()
                ->published()
                ->where('is_best', true)
                ->with(['subcategory:id,name,slug,category_id', 'subcategory.category:id,name,slug'])
                ->latest('id')
                ->take(4)
                ->get(['id', 'title', 'slug', 'body', 'subcategory_id', 'recommended_platform', 'recommended_model', 'is_best']);

            foreach ($bestPrompts as $prompt) {
                $prompt->setAttribute('body', Str::limit(strip_tags((string) $prompt->body), 180));
            }

            $instantPrompts = Prompt::query()
                ->published()
                ->whereHas('subcategory', fn ($q) => $q->where('slug', 'quick-test')
                    ->whereHas('category', fn ($c) => $c->where('slug', 'instant-solutions')))
                ->with(['subcategory:id,name,slug,category_id', 'subcategory.category:id,name,slug'])
                ->orderByDesc('is_best')
                ->orderBy('id')
                ->get(['id', 'title', 'slug', 'body', 'subcategory_id', 'is_best']);

            foreach ($instantPrompts as $prompt) {
                $prompt->setAttribute('body', Str::limit(strip_tags((string) $prompt->body), 140));
            }

            $statsRow = Prompt::query()
                ->selectRaw('COUNT(*) as prompts, SUM(CASE WHEN is_verified = 1 THEN 1 ELSE 0 END) as verified')
                ->published()
                ->first();

            $stats = [
                'prompts' => (int) ($statsRow->prompts ?? 0),
                'verified' => (int) ($statsRow->verified ?? 0),
                'journeys' => Journey::query()->count(),
            ];

            return compact('categories', 'bestPrompts', 'featuredJourneys', 'instantPrompts', 'stats');
        });

        return view('home', $payload);
    }

    public function show(Category $category): View
    {
        $category->load(['subcategories' => fn ($q) => $q->withCount('prompts')]);

        return view('categories.show', compact('category'));
    }
}
