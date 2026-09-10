<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prompt extends Model
{
    protected $fillable = [
        'subcategory_id',
        'title',
        'slug',
        'body',
        'tip_note',
        'recommended_platform',
        'recommended_model',
        'is_best',
        'is_verified',
        'verified_by',
        'verified_at',
        'is_public',
        'is_free',
        'status',
        'copy_count',
    ];

    protected function casts(): array
    {
        return [
            'is_best' => 'boolean',
            'is_verified' => 'boolean',
            'is_public' => 'boolean',
            'is_free' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(PromptVersion::class)->orderByDesc('version');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function recordVersion(?string $changeNote = null): PromptVersion
    {
        $next = ($this->versions()->max('version') ?? 0) + 1;

        return $this->versions()->create([
            'version' => $next,
            'title' => $this->title,
            'body' => $this->body,
            'tip_note' => $this->tip_note,
            'change_note' => $changeNote,
        ]);
    }
}
