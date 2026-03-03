<?php

namespace App\Policies;

use App\Models\Collection;
use App\Models\User;

class CollectionPolicy
{
    public function view(User $user, Collection $collection): bool
    {
        return (int) $user->id === (int) $collection->user_id || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Collection $collection): bool
    {
        return (int) $user->id === (int) $collection->user_id || $user->isAdmin();
    }

    public function delete(User $user, Collection $collection): bool
    {
        return (int) $user->id === (int) $collection->user_id || $user->isAdmin();
    }

    public function restore(User $user, Collection $collection): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Collection $collection): bool
    {
        return $user->isAdmin();
    }
}
