<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Recipes\UploadRecipeMediaRequest;
use App\Models\Recipe;
use App\Models\RecipeMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class RecipeMediaController extends Controller
{
    public function store(UploadRecipeMediaRequest $request, Recipe $recipe): JsonResponse
    {
        $this->authorize('update', $recipe);

        $data = $request->validated();
        $type = $data['type'] ?? 'video';
        $payload = [
            'type' => $type,
            'disk' => 'public',
            'is_primary' => (bool) ($data['is_primary'] ?? false),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'external_url' => $data['external_url'] ?? null,
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $mime = (string) $file->getMimeType();
            $payload['type'] = str_starts_with($mime, 'image/') ? 'image' : 'video';
            $payload['mime_type'] = $mime;
            $payload['size_bytes'] = $file->getSize();
            $payload['path'] = $file->store("recipes/{$recipe->id}", 'public');
            $payload['external_url'] = null;
        }

        $media = $recipe->media()->create($payload);

        if ($media->type === 'image' && ($media->is_primary || ! $recipe->main_image_id)) {
            $this->setPrimaryImage($recipe, $media);
            $media->refresh();
        }

        return response()->json([
            'data' => [
                'id' => $media->id,
                'type' => $media->type,
                'url' => $media->url,
                'path' => $media->path,
                'external_url' => $media->external_url,
                'mime_type' => $media->mime_type,
                'size_bytes' => $media->size_bytes,
                'is_primary' => $media->is_primary,
                'sort_order' => $media->sort_order,
            ],
        ], Response::HTTP_CREATED);
    }

    public function setPrimary(Request $request, Recipe $recipe, RecipeMedia $media): JsonResponse
    {
        $this->authorize('update', $recipe);

        if ($media->recipe_id !== $recipe->id || $media->type !== 'image') {
            return response()->json([
                'message' => 'El recurso de media no pertenece a la receta o no es imagen.',
            ], 422);
        }

        $this->setPrimaryImage($recipe, $media);

        return response()->json([
            'message' => 'Foto principal actualizada.',
        ]);
    }

    public function destroy(Recipe $recipe, RecipeMedia $media): JsonResponse
    {
        $this->authorize('update', $recipe);

        if ($media->recipe_id !== $recipe->id) {
            abort(404);
        }

        $wasPrimary = (int) $recipe->main_image_id === (int) $media->id;

        if ($media->path) {
            Storage::disk($media->disk)->delete($media->path);
        }

        $media->delete();

        if ($wasPrimary) {
            $replacement = $recipe->media()->where('type', 'image')->oldest('id')->first();

            if ($replacement) {
                $this->setPrimaryImage($recipe, $replacement);
            } else {
                $recipe->update(['main_image_id' => null]);
            }
        }

        return response()->json([
            'message' => 'Archivo eliminado.',
        ]);
    }

    private function setPrimaryImage(Recipe $recipe, RecipeMedia $media): void
    {
        DB::transaction(function () use ($recipe, $media): void {
            $recipe->media()->where('type', 'image')->update(['is_primary' => false]);

            $media->update(['is_primary' => true]);
            $recipe->update(['main_image_id' => $media->id]);
        });
    }
}
