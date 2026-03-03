<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Ratings\Actions\UpsertRecipeRatingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Ratings\UpsertRecipeRatingRequest;
use App\Http\Resources\Api\V1\RecipeRatingResource;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecipeRatingController extends Controller
{
    public function index(Request $request, Recipe $recipe)
    {
        $this->authorize('view', $recipe);

        $ratings = $recipe->ratings()
            ->with('user')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return RecipeRatingResource::collection($ratings);
    }

    public function upsert(
        UpsertRecipeRatingRequest $request,
        Recipe $recipe,
        UpsertRecipeRatingAction $upsertRecipeRatingAction
    ): JsonResponse {
        $this->authorize('view', $recipe);

        $rating = $upsertRecipeRatingAction->execute(
            $recipe,
            $request->validated(),
            $request->user('api'),
            hash('sha256', ($request->ip() ?? 'unknown')."|{$recipe->id}")
        );

        $recipe->refresh()->loadAvg('ratings as average_rating', 'stars')->loadCount('ratings');

        return response()->json([
            'data' => RecipeRatingResource::make($rating->loadMissing('user')),
            'recipe_rating_summary' => [
                'average_rating' => round((float) ($recipe->average_rating ?? 0), 2),
                'ratings_count' => $recipe->ratings_count,
            ],
        ], $rating->wasRecentlyCreated ? 201 : 200);
    }
}
