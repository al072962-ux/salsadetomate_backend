<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class RecipeMedia extends Model
{
    use HasFactory;

    protected $table = 'recipe_media';

    protected $fillable = [
        'recipe_id',
        'type',
        'disk',
        'path',
        'external_url',
        'mime_type',
        'size_bytes',
        'is_primary',
        'sort_order',
    ];

    protected $appends = ['url'];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'size_bytes' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function getUrlAttribute(): ?string
    {
        if ($this->external_url) {
            return $this->external_url;
        }

        if (! $this->path) {
            return null;
        }

        return Storage::disk($this->disk)->url($this->path);
    }
}
