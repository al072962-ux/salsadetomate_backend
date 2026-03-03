<?php

namespace App\Policies;

use App\Models\Recipe;
use App\Models\User;

class RecipePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Recipe $recipe): bool
    {
        if ($recipe->status === 'published') {
            return true;
        }

        if (! $user) {
            return false;
        }

        return (int) $user->id === (int) $recipe->user_id || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Recipe $recipe): bool
    {
        return (int) $user->id === (int) $recipe->user_id || $user->isAdmin();
    }

    public function delete(User $user, Recipe $recipe): bool
    {
        return (int) $user->id === (int) $recipe->user_id || $user->isAdmin();
    }

    public function restore(User $user, Recipe $recipe): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Recipe $recipe): bool
    {
        return $user->isAdmin();
    }
}
