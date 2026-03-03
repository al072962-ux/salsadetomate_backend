<?php

namespace App\Domain\Ratings\Actions;

use App\Models\Recipe;
use App\Models\RecipeRating;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UpsertRecipeRatingAction
{
    /**
     * @param array<string, mixed> $payload
     */
    public function execute(Recipe $recipe, array $payload, ?User $user, string $ipHash): RecipeRating
    {
        if ($user) {
            $rating = RecipeRating::query()->updateOrCreate(
                [
                    'recipe_id' => $recipe->id,
                    'user_id' => $user->id,
                ],
                [
                    'stars' => $payload['stars'],
                    'review' => $payload['review'] ?? null,
                    'turned_out_well' => $payload['turned_out_well'] ?? null,
                    'changes' => $payload['changes'] ?? null,
                    'would_cook_again' => $payload['would_cook_again'] ?? null,
                    'guest_name' => null,
                    'guest_email' => null,
                    'ip_hash' => null,
                ]
            );

            return $rating->fresh('user');
        }

        if (empty($payload['guest_name'])) {
            throw ValidationException::withMessages([
                'guest_name' => 'El nombre es obligatorio para calificar sin iniciar sesión.',
            ]);
        }

        $rating = RecipeRating::query()->updateOrCreate(
            [
                'recipe_id' => $recipe->id,
                'ip_hash' => $ipHash,
            ],
            [
                'user_id' => null,
                'guest_name' => $payload['guest_name'],
                'guest_email' => $payload['guest_email'] ?? null,
                'stars' => $payload['stars'],
                'review' => $payload['review'] ?? null,
                'turned_out_well' => $payload['turned_out_well'] ?? null,
                'changes' => $payload['changes'] ?? null,
                'would_cook_again' => $payload['would_cook_again'] ?? null,
            ]
        );

        return $rating;
    }
}
