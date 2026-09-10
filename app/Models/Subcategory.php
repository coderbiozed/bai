<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subcategory extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'sort_order',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function prompts(): HasMany
    {
        return $this->hasMany(Prompt::class)->orderByDesc('is_best')->orderBy('title');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
