<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Comments\StoreRecipeCommentRequest;
use App\Http\Resources\Api\V1\RecipeCommentResource;
use App\Models\Recipe;
use App\Models\RecipeComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RecipeCommentController extends Controller
{
    public function index(Request $request, Recipe $recipe)
    {
        $this->authorize('view', $recipe);

        $comments = $recipe->comments()
            ->whereNull('parent_id')
            ->with('user')
            ->withCount('children')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return RecipeCommentResource::collection($comments);
    }

    /**
     * @throws ValidationException
     */
    public function store(StoreRecipeCommentRequest $request, Recipe $recipe): JsonResponse
    {
        $this->authorize('view', $recipe);

        $data = $request->validated();

        if (!empty($data['parent_id'])) {
            $parent = RecipeComment::query()->find($data['parent_id']);

            if (!$parent || (int) $parent->recipe_id !== (int) $recipe->id) {
                throw ValidationException::withMessages([
                    'parent_id' => 'El comentario padre no pertenece a esta receta.',
                ]);
            }
        }

        $user = $request->user('api');

        $comment = $recipe->comments()->create([
            'user_id' => $user?->id,
            'guest_name' => $user ? null : ($data['guest_name'] ?? 'Invitado'),
            'parent_id' => $data['parent_id'] ?? null,
            'body' => $data['body'],
        ]);

        return response()->json([
            'data' => RecipeCommentResource::make($comment->load('user')),
        ], 201);
    }

    public function destroy(Request $request, Recipe $recipe, RecipeComment $comment): JsonResponse
    {
        if ((int) $comment->recipe_id !== (int) $recipe->id) {
            abort(404);
        }

        $this->authorize('delete', $comment);
        $comment->delete();

        return response()->json([
            'message' => 'Comentario eliminado correctamente.',
        ]);
    }
}
