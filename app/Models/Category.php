<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'accent',
        'sort_order',
    ];

    public function subcategories(): HasMany
    {
        return $this->hasMany(Subcategory::class)->orderBy('sort_order');
    }

    public function prompts(): HasManyThrough
    {
        return $this->hasManyThrough(Prompt::class, Subcategory::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
