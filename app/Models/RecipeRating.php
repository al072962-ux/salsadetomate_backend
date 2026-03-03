<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipe_id',
        'user_id',
        'guest_name',
        'guest_email',
        'ip_hash',
        'stars',
        'review',
        'turned_out_well',
        'changes',
        'would_cook_again',
    ];

    protected function casts(): array
    {
        return [
            'stars' => 'integer',
            'turned_out_well' => 'boolean',
            'would_cook_again' => 'boolean',
        ];
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
