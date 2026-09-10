<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Prompt;
use App\Models\Subcategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PromptController extends Controller
{
    public function show(Category $category, Subcategory $subcategory, Prompt $prompt): View
    {
        abort_unless($subcategory->category_id === $category->id, 404);
        abort_unless($prompt->subcategory_id === $subcategory->id, 404);

        $prompt->load('tags');

        $related = Prompt::query()
            ->published()
            ->where('subcategory_id', $subcategory->id)
            ->where('id', '!=', $prompt->id)
            ->take(3)
            ->get();

        return view('prompts.show', compact('category', 'subcategory', 'prompt', 'related'));
    }

    public function create(): View
    {
        $categories = Category::query()->with('subcategories')->orderBy('sort_order')->get();

        return view('prompts.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subcategory_id' => ['required', 'exists:subcategories,id'],
            'title' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string'],
            'tip_note' => ['nullable', 'string'],
            'recommended_platform' => ['nullable', 'string', 'max:80'],
            'recommended_model' => ['nullable', 'string', 'max:80'],
            'is_best' => ['sometimes', 'boolean'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $subcategory = Subcategory::query()->with('category')->findOrFail($data['subcategory_id']);

        $prompt = Prompt::query()->create([
            'subcategory_id' => $subcategory->id,
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($subcategory->id, $data['title']),
            'body' => $data['body'],
            'tip_note' => $data['tip_note'] ?? null,
            'recommended_platform' => $data['recommended_platform'] ?? null,
            'recommended_model' => $data['recommended_model'] ?? null,
            'is_best' => $request->boolean('is_best'),
            'is_public' => false,
            'is_free' => true,
            'status' => $data['status'],
        ]);

        $prompt->recordVersion('Created');

        return redirect()
            ->route('prompts.show', [$subcategory->category, $subcategory, $prompt])
            ->with('success', 'Prompt saved.');
    }

    public function edit(Category $category, Subcategory $subcategory, Prompt $prompt): View
    {
        abort_unless($subcategory->category_id === $category->id, 404);
        abort_unless($prompt->subcategory_id === $subcategory->id, 404);

        $categories = Category::query()->with('subcategories')->orderBy('sort_order')->get();

        return view('prompts.edit', compact('category', 'subcategory', 'prompt', 'categories'));
    }

    public function update(Request $request, Category $category, Subcategory $subcategory, Prompt $prompt): RedirectResponse
    {
        abort_unless($subcategory->category_id === $category->id, 404);
        abort_unless($prompt->subcategory_id === $subcategory->id, 404);

        $data = $request->validate([
            'subcategory_id' => ['required', 'exists:subcategories,id'],
            'title' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string'],
            'tip_note' => ['nullable', 'string'],
            'recommended_platform' => ['nullable', 'string', 'max:80'],
            'recommended_model' => ['nullable', 'string', 'max:80'],
            'is_best' => ['sometimes', 'boolean'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $newSubcategory = Subcategory::query()->with('category')->findOrFail($data['subcategory_id']);

        $prompt->fill([
            'subcategory_id' => $newSubcategory->id,
            'title' => $data['title'],
            'body' => $data['body'],
            'tip_note' => $data['tip_note'] ?? null,
            'recommended_platform' => $data['recommended_platform'] ?? null,
            'recommended_model' => $data['recommended_model'] ?? null,
            'is_best' => $request->boolean('is_best'),
            'status' => $data['status'],
        ]);

        if ($prompt->isDirty('title') || $prompt->isDirty('subcategory_id')) {
            $prompt->slug = $this->uniqueSlug($newSubcategory->id, $data['title'], $prompt->id);
        }

        $prompt->save();
        $prompt->recordVersion('Updated');

        return redirect()
            ->route('prompts.show', [$newSubcategory->category, $newSubcategory, $prompt])
            ->with('success', 'Prompt updated.');
    }

    public function destroy(Category $category, Subcategory $subcategory, Prompt $prompt): RedirectResponse
    {
        abort_unless($subcategory->category_id === $category->id, 404);
        abort_unless($prompt->subcategory_id === $subcategory->id, 404);

        $prompt->delete();

        return redirect()
            ->route('subcategories.show', [$category, $subcategory])
            ->with('success', 'Prompt deleted.');
    }

    public function copy(Prompt $prompt): RedirectResponse
    {
        $prompt->increment('copy_count');

        return back()->with('copied', true);
    }

    private function uniqueSlug(int $subcategoryId, string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'prompt';
        $slug = $base;
        $i = 2;

        while (
            Prompt::query()
                ->where('subcategory_id', $subcategoryId)
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
