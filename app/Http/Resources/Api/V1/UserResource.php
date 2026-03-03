<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->when($request->user('api')?->id === $this->id, $this->email),
            'role' => $this->role,
            'bio' => $this->bio,
            'avatar_url' => $this->avatar_url,
            'followers_count' => $this->whenCounted('followers'),
            'following_count' => $this->whenCounted('following'),
            'recipes_count' => $this->whenCounted('recipes'),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
