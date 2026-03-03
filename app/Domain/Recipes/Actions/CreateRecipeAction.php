<?php

namespace App\Domain\Recipes\Actions;

use App\Domain\Recipes\Support\NutritionCalculator;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateRecipeAction
{
    public function __construct(private readonly NutritionCalculator $nutritionCalculator)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function execute(User $user, array $payload): Recipe
    {
        return DB::transaction(function () use ($user, $payload): Recipe {
            $recipe = Recipe::create([
                'user_id' => $user->id,
                'title' => $payload['title'],
                'summary' => $payload['summary'] ?? null,
                'description' => $payload['description'] ?? null,
                'servings' => $payload['servings'] ?? null,
                'prep_time_minutes' => $payload['prep_time_minutes'] ?? null,
                'cook_time_minutes' => $payload['cook_time_minutes'] ?? null,
                'status' => 'draft',
                'video_type' => $payload['video_type'] ?? null,
                'video_url' => $payload['video_url'] ?? null,
            ]);

            $this->syncCategories($recipe, Arr::wrap($payload['category_ids'] ?? []));
            $ingredients = Arr::wrap($payload['ingredients'] ?? []);
            $this->syncIngredients($recipe, $ingredients);
            $this->syncSteps($recipe, Arr::wrap($payload['steps'] ?? []));

            $recipe->update([
                'nutrition' => $this->nutritionCalculator->calculate($ingredients),
            ]);

            return $recipe->load(['categories', 'ingredients', 'steps', 'mainImage', 'media']);
        });
    }

    /**
     * @param array<int, int|string> $categoryIds
     */
    private function syncCategories(Recipe $recipe, array $categoryIds): void
    {
        $ids = collect($categoryIds)->filter()->map(static fn ($id): int => (int) $id)->values()->all();
        $recipe->categories()->sync($ids);
    }

    /**
     * @param array<int, array<string, mixed>> $ingredients
     */
    private function syncIngredients(Recipe $recipe, array $ingredients): void
    {
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
    }

    /**
     * @param array<int, array<string, mixed>> $steps
     */
    private function syncSteps(Recipe $recipe, array $steps): void
    {
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
}
