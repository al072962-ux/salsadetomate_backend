<?php

namespace App\Policies;

use App\Models\RecipeComment;
use App\Models\User;

class RecipeCommentPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, RecipeComment $recipeComment): bool
    {
        if ($recipeComment->recipe?->status === 'published') {
            return true;
        }

        if (! $user) {
            return false;
        }

        return (int) $user->id === (int) $recipeComment->user_id
            || (int) $user->id === (int) $recipeComment->recipe?->user_id
            || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, RecipeComment $recipeComment): bool
    {
        return (int) $user->id === (int) $recipeComment->user_id || $user->isAdmin();
    }

    public function delete(User $user, RecipeComment $recipeComment): bool
    {
        return (int) $user->id === (int) $recipeComment->user_id
            || (int) $user->id === (int) $recipeComment->recipe?->user_id
            || $user->isAdmin();
    }

    public function restore(User $user, RecipeComment $recipeComment): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, RecipeComment $recipeComment): bool
    {
        return $user->isAdmin();
    }
}
