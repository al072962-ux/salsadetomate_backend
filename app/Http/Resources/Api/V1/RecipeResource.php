<?php

namespace App\Http\Resources\Api\V1;

use App\Models\RecipeMedia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Recipe */
class RecipeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $average = $this->average_rating ?? $this->ratings_avg_stars;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'summary' => $this->summary,
            'description' => $this->description,
            'servings' => $this->servings,
            'prep_time_minutes' => $this->prep_time_minutes,
            'cook_time_minutes' => $this->cook_time_minutes,
            'status' => $this->status,
            'published_at' => $this->published_at?->toISOString(),
            'video_type' => $this->video_type,
            'video_url' => $this->video_url,
            'nutrition' => $this->nutrition,
            'average_rating' => $average !== null ? round((float) $average, 2) : null,
            'ratings_count' => $this->ratings_count ?? null,
            'likes_count' => $this->likes_count ?? null,
            'comments_count' => $this->comments_count ?? null,
            'author' => $this->whenLoaded('user', fn () => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ]),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'ingredients' => $this->whenLoaded('ingredients', fn () => $this->ingredients->map(static fn ($ingredient) => [
                'id' => $ingredient->id,
                'position' => $ingredient->position,
                'name' => $ingredient->name,
                'quantity' => $ingredient->quantity,
                'unit' => $ingredient->unit,
                'notes' => $ingredient->notes,
                'calories' => $ingredient->calories,
                'protein_g' => $ingredient->protein_g,
                'carbs_g' => $ingredient->carbs_g,
                'fat_g' => $ingredient->fat_g,
            ])),
            'steps' => $this->whenLoaded('steps', fn () => $this->steps->map(static fn ($step) => [
                'id' => $step->id,
                'step_number' => $step->step_number,
                'instruction' => $step->instruction,
                'image_path' => $step->image_path,
            ])),
            'main_image' => $this->whenLoaded('mainImage', fn () => $this->mainImage ? $this->formatMedia($this->mainImage) : null),
            'media' => $this->whenLoaded('media', fn () => $this->media->map(fn (RecipeMedia $media) => $this->formatMedia($media))),
            'ratings' => RecipeRatingResource::collection($this->whenLoaded('ratings')),
            'comments' => RecipeCommentResource::collection($this->whenLoaded('comments')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatMedia(RecipeMedia $media): array
    {
        return [
            'id' => $media->id,
            'type' => $media->type,
            'url' => $media->url,
            'path' => $media->path,
            'external_url' => $media->external_url,
            'mime_type' => $media->mime_type,
            'size_bytes' => $media->size_bytes,
            'is_primary' => $media->is_primary,
            'sort_order' => $media->sort_order,
        ];
    }
}
