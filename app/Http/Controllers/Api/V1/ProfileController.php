<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Profile\UploadAvatarRequest;
use App\Http\Requests\Api\V1\Profile\UpdateProfileRequest;
use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function show(Request $request): UserResource
    {
        $user = $request->user('api')->loadCount(['followers', 'following', 'recipes']);

        return UserResource::make($user);
    }

    public function update(UpdateProfileRequest $request): UserResource
    {
        $user = $request->user('api');
        $user->update($request->validated());

        return UserResource::make($user->fresh()->loadCount(['followers', 'following', 'recipes']));
    }

    public function uploadAvatar(UploadAvatarRequest $request): UserResource
    {
        $user = $request->user('api');
        $oldPath = $this->extractPublicStoragePath($user->avatar_url);

        $path = $request->file('avatar')->store("avatars/{$user->id}", 'public');

        $user->update([
            'avatar_url' => Storage::disk('public')->url($path),
        ]);

        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        return UserResource::make($user->fresh()->loadCount(['followers', 'following', 'recipes']));
    }

    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $request->user('api');
        $oldPath = $this->extractPublicStoragePath($user->avatar_url);

        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $user->update([
            'avatar_url' => null,
        ]);

        return response()->json([
            'message' => 'Avatar eliminado correctamente.',
            'user' => UserResource::make($user->fresh()->loadCount(['followers', 'following', 'recipes'])),
        ]);
    }

    private function extractPublicStoragePath(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || ! Str::startsWith($path, '/storage/')) {
            return null;
        }

        return ltrim(Str::after($path, '/storage/'), '/');
    }
}
