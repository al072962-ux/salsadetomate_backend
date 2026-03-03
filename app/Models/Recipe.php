<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'summary',
        'description',
        'servings',
        'prep_time_minutes',
        'cook_time_minutes',
        'status',
        'published_at',
        'main_image_id',
        'video_type',
        'video_url',
        'nutrition',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'nutrition' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class)->orderBy('position');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(RecipeStep::class)->orderBy('step_number');
    }

    public function media(): HasMany
    {
        return $this->hasMany(RecipeMedia::class)->orderBy('sort_order');
    }

    public function mainImage(): BelongsTo
    {
        return $this->belongsTo(RecipeMedia::class, 'main_image_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(RecipeRating::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(RecipeComment::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(RecipeLike::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
