<?php

namespace App\Domain\Recipes\Actions;

use App\Models\Recipe;
use Illuminate\Validation\ValidationException;

class PublishRecipeAction
{
    /**
     * @throws ValidationException
     */
    public function execute(Recipe $recipe): Recipe
    {
        $recipe->loadMissing(['mainImage', 'steps', 'ingredients']);

        if (! $recipe->mainImage || $recipe->mainImage->type !== 'image') {
            throw ValidationException::withMessages([
                'main_image' => 'La receta debe tener una foto principal antes de publicarse.',
            ]);
        }

        if ($recipe->steps->isEmpty()) {
            throw ValidationException::withMessages([
                'steps' => 'La receta necesita al menos un paso para publicarse.',
            ]);
        }

        if ($recipe->ingredients->isEmpty()) {
            throw ValidationException::withMessages([
                'ingredients' => 'La receta necesita al menos un ingrediente para publicarse.',
            ]);
        }

        $recipe->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return $recipe->fresh([
            'user',
            'categories',
            'ingredients',
            'steps',
            'mainImage',
            'media',
        ]);
    }
}
