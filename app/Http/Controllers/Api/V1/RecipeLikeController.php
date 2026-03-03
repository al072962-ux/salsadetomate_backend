<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;

class RecipeLikeController extends Controller
{
    public function store(Recipe $recipe): JsonResponse
    {
        $this->authorize('view', $recipe);

        $user = auth('api')->user();

        $recipe->likes()->firstOrCreate([
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Receta guardada con me gusta.',
            'likes_count' => $recipe->likes()->count(),
        ], 201);
    }

    public function destroy(Recipe $recipe): JsonResponse
    {
        $user = auth('api')->user();

        $recipe->likes()->where('user_id', $user->id)->delete();

        return response()->json([
            'message' => 'Me gusta eliminado.',
            'likes_count' => $recipe->likes()->count(),
        ]);
    }
}
