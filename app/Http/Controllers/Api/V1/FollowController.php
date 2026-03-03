<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class FollowController extends Controller
{
    public function store(User $user): JsonResponse
    {
        $authUser = auth('api')->user();

        if ((int) $authUser->id === (int) $user->id) {
            return response()->json([
                'message' => 'No puedes seguirte a ti mismo.',
            ], 422);
        }

        Follow::query()->firstOrCreate([
            'follower_id' => $authUser->id,
            'followed_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Ahora sigues a este usuario.',
        ], 201);
    }

    public function destroy(User $user): JsonResponse
    {
        $authUser = auth('api')->user();

        Follow::query()
            ->where('follower_id', $authUser->id)
            ->where('followed_id', $user->id)
            ->delete();

        return response()->json([
            'message' => 'Has dejado de seguir a este usuario.',
        ]);
    }
}
