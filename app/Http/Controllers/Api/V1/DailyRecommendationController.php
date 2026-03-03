<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Feed\Actions\GetDailyRecommendationAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\RecipeResource;
use Illuminate\Http\Request;

class DailyRecommendationController extends Controller
{
    public function index(Request $request, GetDailyRecommendationAction $getDailyRecommendationAction)
    {
        $recipes = $getDailyRecommendationAction->execute((int) $request->integer('limit', 1));

        return RecipeResource::collection($recipes);
    }
}
