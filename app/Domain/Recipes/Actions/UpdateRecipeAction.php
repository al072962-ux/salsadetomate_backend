<?php

namespace App\Domain\Recipes\Actions;

use App\Domain\Recipes\Support\NutritionCalculator;
use App\Models\Recipe;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateRecipeAction
{
    public function __construct(private readonly NutritionCalculator $nutritionCalculator)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function execute(Recipe $recipe, array $payload): Recipe
    {
        return DB::transaction(function () use ($recipe, $payload): Recipe {
            $recipe->update([
                'title' => $payload['title'] ?? $recipe->title,
                'summary' => $payload['summary'] ?? $recipe->summary,
                'description' => $payload['description'] ?? $recipe->description,
                'servings' => $payload['servings'] ?? $recipe->servings,
                'prep_time_minutes' => $payload['prep_time_minutes'] ?? $recipe->prep_time_minutes,
                'cook_time_minutes' => $payload['cook_time_minutes'] ?? $recipe->cook_time_minutes,
                'video_type' => $payload['video_type'] ?? $recipe->video_type,
                'video_url' => $payload['video_url'] ?? $recipe->video_url,
            ]);

            if (array_key_exists('category_ids', $payload)) {
                $categoryIds = collect(Arr::wrap($payload['category_ids']))
                    ->filter()
                    ->map(static fn ($id): int => (int) $id)
                    ->values()
                    ->all();

                $recipe->categories()->sync($categoryIds);
            }

            if (array_key_exists('ingredients', $payload)) {
                $ingredients = Arr::wrap($payload['ingredients']);
                $recipe->ingredients()->delete();

                foreach ($ingredients as $index => $ingredient) {
                    if (! isset($ingredient['name'])) {
                        continue;
                    }

                    $recipe->ingredients()->create([
                        'position' => $index + 1,
                        'name' => $ingredient['name'],
                        'quantity' => $ingredient['quantity'] ?? null,
                        'unit' => $ingredient['unit'] ?? null,
                        'notes' => $ingredient['notes'] ?? null,
                        'calories' => $ingredient['calories'] ?? null,
                        'protein_g' => $ingredient['protein_g'] ?? null,
                        'carbs_g' => $ingredient['carbs_g'] ?? null,
                        'fat_g' => $ingredient['fat_g'] ?? null,
                    ]);
                }

                $recipe->nutrition = $this->nutritionCalculator->calculate($ingredients);
            }

            if (array_key_exists('steps', $payload)) {
                $steps = Arr::wrap($payload['steps']);
                $recipe->steps()->delete();

                foreach ($steps as $index => $step) {
                    if (! isset($step['instruction'])) {
                        continue;
                    }

                    $recipe->steps()->create([
                        'step_number' => $index + 1,
                        'instruction' => $step['instruction'],
                        'image_path' => $step['image_path'] ?? null,
                    ]);
                }
            }

            $recipe->save();

            return $recipe->load(['categories', 'ingredients', 'steps', 'mainImage', 'media']);
        });
    }
}
