<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CollectionController;
use App\Http\Controllers\Api\V1\DailyRecommendationController;
use App\Http\Controllers\Api\V1\FollowController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\RecipeCommentController;
use App\Http\Controllers\Api\V1\RecipeController;
use App\Http\Controllers\Api\V1\RecipeLikeController;
use App\Http\Controllers\Api\V1\RecipeMediaController;
use App\Http\Controllers\Api\V1\RecipeRatingController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function (): void {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::get('/recipes', [RecipeController::class, 'index']);
Route::get('/recipes/{recipe}', [RecipeController::class, 'show']);
Route::get('/recipes/{recipe}/ratings', [RecipeRatingController::class, 'index']);
Route::post('/recipes/{recipe}/ratings', [RecipeRatingController::class, 'upsert']);
Route::get('/recipes/{recipe}/comments', [RecipeCommentController::class, 'index']);
Route::post('/recipes/{recipe}/comments', [RecipeCommentController::class, 'store']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/feed/daily-recommendation', [DailyRecommendationController::class, 'index']);

Route::middleware('auth:api')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar']);
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar']);

    Route::post('/categories', [CategoryController::class, 'store']);

    Route::post('/recipes', [RecipeController::class, 'store']);
    Route::put('/recipes/{recipe}', [RecipeController::class, 'update']);
    Route::patch('/recipes/{recipe}', [RecipeController::class, 'update']);
    Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy']);
    Route::post('/recipes/{recipe}/publish', [RecipeController::class, 'publish']);
    Route::post('/recipes/{recipe}/unpublish', [RecipeController::class, 'unpublish']);

    Route::post('/recipes/{recipe}/media', [RecipeMediaController::class, 'store']);
    Route::patch('/recipes/{recipe}/media/{media}/primary', [RecipeMediaController::class, 'setPrimary']);
    Route::delete('/recipes/{recipe}/media/{media}', [RecipeMediaController::class, 'destroy']);

    Route::delete('/recipes/{recipe}/comments/{comment}', [RecipeCommentController::class, 'destroy']);

    Route::post('/recipes/{recipe}/like', [RecipeLikeController::class, 'store']);
    Route::delete('/recipes/{recipe}/like', [RecipeLikeController::class, 'destroy']);

    Route::post('/users/{user}/follow', [FollowController::class, 'store']);
    Route::delete('/users/{user}/follow', [FollowController::class, 'destroy']);

    Route::get('/collections', [CollectionController::class, 'index']);
    Route::post('/collections', [CollectionController::class, 'store']);
    Route::get('/collections/{collection}', [CollectionController::class, 'show']);
    Route::post('/collections/{collection}/recipes', [CollectionController::class, 'attachRecipe']);
    Route::delete('/collections/{collection}/recipes/{recipe}', [CollectionController::class, 'detachRecipe']);
    Route::delete('/collections/{collection}', [CollectionController::class, 'destroy']);
});
