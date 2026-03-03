<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Recipes\Actions\CreateRecipeAction;
use App\Domain\Recipes\Actions\PublishRecipeAction;
use App\Domain\Recipes\Actions\UpdateRecipeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Recipes\RecipeIndexRequest;
use App\Http\Requests\Api\V1\Recipes\StoreRecipeRequest;
use App\Http\Requests\Api\V1\Recipes\UpdateRecipeRequest;
use App\Http\Resources\Api\V1\RecipeResource;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    public function index(RecipeIndexRequest $request)
    {
        $user = $request->user('api');
        $data = $request->validated();

        $query = Recipe::query()
            ->with(['user', 'mainImage', 'categories'])
            ->withAvg('ratings as average_rating', 'stars')
            ->withCount(['ratings', 'likes', 'comments']);

        $isMine = (bool) ($data['mine'] ?? false);

        if ($isMine && $user) {
            $query->where('user_id', $user->id);

            if (!empty($data['status'])) {
                $query->where('status', $data['status']);
            }
        } else {
            $query->published();
        }

        if (!empty($data['q'])) {
            $term = $data['q'];
            $query->where(function ($q) use ($term): void {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('summary', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhereHas('ingredients', fn($ingredientQuery) => $ingredientQuery->where('name', 'like', "%{$term}%"));
            });
        }

        if (!empty($data['author_id'])) {
            $query->where('user_id', $data['author_id']);
        }

        if (!empty($data['category_ids'])) {
            $categoryIds = collect($data['category_ids'])->map(static fn($id): int => (int) $id)->all();
            $query->whereHas('categories', fn($q) => $q->whereIn('categories.id', $categoryIds));
        }

        if (!empty($data['published_from'])) {
            $query->whereDate('published_at', '>=', $data['published_from']);
        }

        if (!empty($data['published_to'])) {
            $query->whereDate('published_at', '<=', $data['published_to']);
        }

        $recipes = $query
            ->latest('published_at')
            ->latest('created_at')
            ->paginate((int) ($data['per_page'] ?? 12))
            ->withQueryString();

        return RecipeResource::collection($recipes);
    }

    public function show(Request $request, Recipe $recipe): RecipeResource
    {
        $user = $request->user('api');

        if ($recipe->status !== 'published') {
            if (!$user || ((int) $user->id !== (int) $recipe->user_id && !$user->isAdmin())) {
                abort(403, 'No tienes permiso para ver esta receta en borrador.');
            }
        }

        return RecipeResource::make(
            $recipe->load([
                'user',
                'categories',
                'ingredients',
                'steps',
                'mainImage',
                'media',
                'ratings.user',
                'comments.user',
            ])->loadAvg('ratings as average_rating', 'stars')
                ->loadCount(['ratings', 'likes', 'comments'])
        );
    }

    public function store(
        StoreRecipeRequest $request,
        CreateRecipeAction $createRecipeAction,
        PublishRecipeAction $publishRecipeAction
    ): JsonResponse {
        $recipe = $createRecipeAction->execute($request->user('api'), $request->validated());

        if (($request->validated()['status'] ?? null) === 'published') {
            $recipe = $publishRecipeAction->execute($recipe);
        }

        $recipe->load(['user', 'categories', 'ingredients', 'steps', 'mainImage', 'media'])
            ->loadAvg('ratings as average_rating', 'stars')
            ->loadCount(['ratings', 'likes', 'comments']);

        return RecipeResource::make($recipe)->response()->setStatusCode(201);
    }

    public function update(
        UpdateRecipeRequest $request,
        Recipe $recipe,
        UpdateRecipeAction $updateRecipeAction,
        PublishRecipeAction $publishRecipeAction
    ): RecipeResource {
        $this->authorize('update', $recipe);

        $recipe = $updateRecipeAction->execute($recipe, $request->validated());

        $status = $request->validated()['status'] ?? null;

        if ($status === 'published') {
            $recipe = $publishRecipeAction->execute($recipe);
        }

        if ($status === 'draft') {
            $recipe->update([
                'status' => 'draft',
                'published_at' => null,
            ]);
        }

        return RecipeResource::make(
            $recipe->fresh([
                'user',
                'categories',
                'ingredients',
                'steps',
                'mainImage',
                'media',
            ])->loadAvg('ratings as average_rating', 'stars')
                ->loadCount(['ratings', 'likes', 'comments'])
        );
    }

    public function destroy(Recipe $recipe): JsonResponse
    {
        $this->authorize('delete', $recipe);

        $recipe->delete();

        return response()->json([
            'message' => 'Receta eliminada correctamente.',
        ]);
    }

    public function publish(Recipe $recipe, PublishRecipeAction $publishRecipeAction): RecipeResource
    {
        $this->authorize('update', $recipe);

        $recipe = $publishRecipeAction->execute($recipe);

        return RecipeResource::make(
            $recipe->loadAvg('ratings as average_rating', 'stars')
                ->loadCount(['ratings', 'likes', 'comments'])
        );
    }

    public function unpublish(Recipe $recipe): RecipeResource
    {
        $this->authorize('update', $recipe);

        $recipe->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        return RecipeResource::make(
            $recipe->fresh([
                'user',
                'categories',
                'ingredients',
                'steps',
                'mainImage',
                'media',
            ])->loadAvg('ratings as average_rating', 'stars')
                ->loadCount(['ratings', 'likes', 'comments'])
        );
    }
}
