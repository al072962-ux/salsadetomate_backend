<?php

namespace App\Domain\Feed\Actions;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Collection;

class GetDailyRecommendationAction
{
    public function execute(int $limit = 1): Collection
    {
        $limit = max(1, min($limit, 10));
        $minAverage = (float) config('recipes.daily_min_average', 4);
        $minRatings = (int) config('recipes.daily_min_ratings', 10);

        $recipes = Recipe::query()
            ->published()
            ->with(['mainImage', 'categories', 'user'])
            ->withAvg('ratings as average_rating', 'stars')
            ->withCount('ratings')
            ->whereHas('ratings', function ($query) use ($minAverage, $minRatings): void {
                $query->selectRaw('recipe_id')
                    ->groupBy('recipe_id')
                    ->havingRaw('AVG(stars) >= ?', [$minAverage])
                    ->havingRaw('COUNT(*) >= ?', [$minRatings]);
            })
            ->inRandomOrder()
            ->limit($limit)
            ->get();

        if ($recipes->isNotEmpty()) {
            return $recipes;
        }

        return Recipe::query()
            ->published()
            ->with(['mainImage', 'categories', 'user'])
            ->withAvg('ratings as average_rating', 'stars')
            ->withCount('ratings')
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }
}
