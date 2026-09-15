<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SubcategoryController extends Controller
{
    public function show(Category $category, Subcategory $subcategory): View
    {
        abort_unless($subcategory->category_id === $category->id, 404);

        $cacheKey = "subcat.{$subcategory->id}.list.v1";

        $prompts = Cache::remember($cacheKey, 180, function () use ($subcategory) {
            $prompts = $subcategory->prompts()
                ->published()
                ->with('tags:id,name,slug')
                ->orderByDesc('is_best')
                ->orderBy('title')
                ->get([
                    'id',
                    'title',
                    'slug',
                    'body',
                    'subcategory_id',
                    'is_best',
                    'is_verified',
                    'is_free',
                    'recommended_platform',
                    'recommended_model',
                ]);

            foreach ($prompts as $prompt) {
                $prompt->setAttribute('body', Str::limit(strip_tags((string) $prompt->body), 220));
            }

            return $prompts;
        });

        $subcategory->setRelation('prompts', $prompts);

        return view('subcategories.show', compact('category', 'subcategory'));
    }
}
