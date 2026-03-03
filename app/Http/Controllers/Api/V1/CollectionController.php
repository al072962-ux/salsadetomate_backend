<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Collections\AttachRecipeRequest;
use App\Http\Requests\Api\V1\Collections\StoreCollectionRequest;
use App\Http\Resources\Api\V1\CollectionResource;
use App\Models\Collection;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;

class CollectionController extends Controller
{
    public function index()
    {
        $collections = auth('api')->user()
            ->collections()
            ->withCount('recipes')
            ->latest()
            ->paginate(20);

        return CollectionResource::collection($collections);
    }

    public function store(StoreCollectionRequest $request): JsonResponse
    {
        $collection = auth('api')->user()->collections()->create($request->validated());

        return response()->json([
            'data' => CollectionResource::make($collection->loadCount('recipes')),
        ], 201);
    }

    public function show(Collection $collection): CollectionResource
    {
        $this->authorize('view', $collection);

        return CollectionResource::make($collection->load(['recipes.mainImage', 'recipes.user'])->loadCount('recipes'));
    }

    public function attachRecipe(AttachRecipeRequest $request, Collection $collection): CollectionResource
    {
        $this->authorize('update', $collection);

        $recipe = Recipe::query()->findOrFail($request->validated()['recipe_id']);
        $user = auth('api')->user();

        if ($recipe->status !== 'published' && (int) $recipe->user_id !== (int) $user->id && ! $user->isAdmin()) {
            abort(404);
        }

        $collection->recipes()->syncWithoutDetaching([$recipe->id]);

        return CollectionResource::make($collection->fresh()->loadCount('recipes'));
    }

    public function detachRecipe(Collection $collection, Recipe $recipe): CollectionResource
    {
        $this->authorize('update', $collection);

        $collection->recipes()->detach($recipe->id);

        return CollectionResource::make($collection->fresh()->loadCount('recipes'));
    }

    public function destroy(Collection $collection): JsonResponse
    {
        $this->authorize('delete', $collection);
        $collection->delete();

        return response()->json([
            'message' => 'Colección eliminada.',
        ]);
    }
}
