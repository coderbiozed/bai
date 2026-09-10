<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Journey extends Model
{
    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'tagline',
        'description',
        'outcome',
        'is_featured',
        'is_verified',
        'is_free',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_verified' => 'boolean',
            'is_free' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(JourneyStep::class)->orderBy('step_number');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
