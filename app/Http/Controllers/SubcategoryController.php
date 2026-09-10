<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\View\View;

class SubcategoryController extends Controller
{
    public function show(Category $category, Subcategory $subcategory): View
    {
        abort_unless($subcategory->category_id === $category->id, 404);

        $subcategory->load(['prompts' => fn ($q) => $q->published()->with('tags')]);

        return view('subcategories.show', compact('category', 'subcategory'));
    }
}
