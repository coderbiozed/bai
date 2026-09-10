@php
    $selectedSubcategoryId = old('subcategory_id', $prompt->subcategory_id ?? request('subcategory_id'));
@endphp

<div class="space-y-6">
    <div>
        <label for="subcategory_id" class="mb-2 block text-sm font-semibold text-ink">Specialty</label>
        <select id="subcategory_id" name="subcategory_id" required
                class="w-full rounded-2xl border border-white/20 bg-white text-deep px-4 py-3 text-sm outline-none focus:border-teal">
            <option value="">Select category → specialty</option>
            @foreach ($categories as $cat)
                <optgroup label="{{ $cat->name }}">
                    @foreach ($cat->subcategories as $sub)
                        <option value="{{ $sub->id }}" @selected((string) $selectedSubcategoryId === (string) $sub->id)>
                            {{ $sub->name }}
                        </option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        @error('subcategory_id') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="title" class="mb-2 block text-sm font-semibold text-ink">Title</label>
        <input id="title" name="title" type="text" required maxlength="180"
               value="{{ old('title', $prompt->title ?? '') }}"
               placeholder="e.g. Principal Laravel Architect — Enterprise"
               class="w-full rounded-2xl border border-white/20 bg-white text-deep px-4 py-3 text-sm outline-none focus:border-teal">
        @error('title') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="body" class="mb-2 block text-sm font-semibold text-ink">Prompt body</label>
        <textarea id="body" name="body" rows="10" required
                  placeholder="Act as a … with expertise in … Prefer … Avoid …"
                  class="w-full rounded-2xl border border-white/20 bg-white text-deep px-4 py-3 font-mono text-sm leading-relaxed outline-none focus:border-teal">{{ old('body', $prompt->body ?? '') }}</textarea>
        @error('body') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="tip_note" class="mb-2 block text-sm font-semibold text-ink">Tip note (optional)</label>
        <textarea id="tip_note" name="tip_note" rows="3"
                  placeholder="Why this prompt works…"
                  class="w-full rounded-2xl border border-white/20 bg-white text-deep px-4 py-3 text-sm outline-none focus:border-teal">{{ old('tip_note', $prompt->tip_note ?? '') }}</textarea>
        @error('tip_note') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="recommended_platform" class="mb-2 block text-sm font-semibold text-ink">Best AI platform</label>
            <input id="recommended_platform" name="recommended_platform" type="text" maxlength="80"
                   value="{{ old('recommended_platform', $prompt->recommended_platform ?? '') }}"
                   placeholder="e.g. ChatGPT, Claude, Midjourney, Cursor"
                   class="w-full rounded-2xl border border-white/20 bg-white text-deep px-4 py-3 text-sm outline-none focus:border-teal">
            @error('recommended_platform') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="recommended_model" class="mb-2 block text-sm font-semibold text-ink">Best model</label>
            <input id="recommended_model" name="recommended_model" type="text" maxlength="80"
                   value="{{ old('recommended_model', $prompt->recommended_model ?? '') }}"
                   placeholder="e.g. GPT-5, Sonnet 4, v6.1"
                   class="w-full rounded-2xl border border-white/20 bg-white text-deep px-4 py-3 text-sm outline-none focus:border-teal">
            @error('recommended_model') <p class="mt-2 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap items-center gap-5">
            <label class="inline-flex items-center gap-2 text-sm text-white/85">
                <input type="checkbox" name="is_best" value="1" class="rounded border-ink/20 text-teal focus:ring-teal"
                       @checked(old('is_best', $prompt->is_best ?? false))>
                Mark as best
            </label>

            <label class="inline-flex items-center gap-2 text-sm text-white/85">
                Status
                <select name="status" class="rounded-xl border border-white/20 bg-white text-deep px-3 py-2 text-sm outline-none focus:border-teal">
                    <option value="published" @selected(old('status', $prompt->status ?? 'published') === 'published')>Published</option>
                    <option value="draft" @selected(old('status', $prompt->status ?? 'published') === 'draft')>Draft</option>
                </select>
            </label>
        </div>

        <button type="submit" class="btn-primary">{{ $submitLabel }}</button>
    </div>
</div>
