<?php

namespace App\Http\Controllers;

use App\Models\Prompt;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $prompts = Prompt::query()
            ->published()
            ->with(['subcategory.category', 'tags'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('title', 'like', "%{$q}%")
                        ->orWhere('body', 'like', "%{$q}%")
                        ->orWhere('tip_note', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('is_best')
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        return view('search', compact('q', 'prompts'));
    }
}
