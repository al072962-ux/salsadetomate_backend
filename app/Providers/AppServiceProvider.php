<?php

namespace App\Providers;

use App\Models\Collection;
use App\Models\Recipe;
use App\Models\RecipeComment;
use App\Policies\CollectionPolicy;
use App\Policies\RecipeCommentPolicy;
use App\Policies\RecipePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Recipe::class, RecipePolicy::class);
        Gate::policy(RecipeComment::class, RecipeCommentPolicy::class);
        Gate::policy(Collection::class, CollectionPolicy::class);
    }
}
