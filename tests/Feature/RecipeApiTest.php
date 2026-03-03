<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeRating;
use App\Models\RecipeStep;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RecipeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_rate_a_published_recipe(): void
    {
        $author = User::factory()->create();

        $recipe = Recipe::query()->create([
            'user_id' => $author->id,
            'title' => 'Sopa de tomate',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->postJson("/api/recipes/{$recipe->id}/ratings", [
            'stars' => 5,
            'guest_name' => 'Doña Rosa',
            'review' => 'Muy rica',
        ]);

        $response->assertCreated()->assertJsonPath('data.stars', 5);

        $this->assertDatabaseHas('recipe_ratings', [
            'recipe_id' => $recipe->id,
            'guest_name' => 'Doña Rosa',
            'stars' => 5,
        ]);
    }

    public function test_recipe_cannot_be_published_without_main_image(): void
    {
        $user = User::factory()->create();

        $token = Str::random(80);
        $user->forceFill(['api_token' => hash('sha256', $token)])->save();

        $recipe = Recipe::query()->create([
            'user_id' => $user->id,
            'title' => 'Huevos rancheros',
            'status' => 'draft',
        ]);

        RecipeIngredient::query()->create([
            'recipe_id' => $recipe->id,
            'position' => 1,
            'name' => 'Huevo',
        ]);

        RecipeStep::query()->create([
            'recipe_id' => $recipe->id,
            'step_number' => 1,
            'instruction' => 'Freír el huevo',
        ]);

        $response = $this
            ->withToken($token)
            ->postJson("/api/recipes/{$recipe->id}/publish");

        $response->assertStatus(422)->assertJsonValidationErrors('main_image');
    }

    public function test_public_list_filters_by_keyword_and_category(): void
    {
        $author = User::factory()->create();
        $category = Category::query()->create([
            'name' => 'Desayunos',
            'slug' => 'desayunos',
            'is_global' => true,
        ]);

        $matching = Recipe::query()->create([
            'user_id' => $author->id,
            'title' => 'Salsa de tomate casera',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $matching->categories()->attach($category->id);

        RecipeIngredient::query()->create([
            'recipe_id' => $matching->id,
            'position' => 1,
            'name' => 'Tomate',
        ]);

        $nonMatching = Recipe::query()->create([
            'user_id' => $author->id,
            'title' => 'Pastel de vainilla',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->getJson(
            "/api/recipes?q=tomate&category_ids[]={$category->id}&per_page=10"
        );

        $response->assertOk();
        $response->assertJsonFragment(['id' => $matching->id]);
        $response->assertJsonMissing(['id' => $nonMatching->id]);
    }

    public function test_daily_recommendation_uses_rating_threshold(): void
    {
        config([
            'recipes.daily_min_average' => 4,
            'recipes.daily_min_ratings' => 2,
        ]);

        $author = User::factory()->create();

        $recipe = Recipe::query()->create([
            'user_id' => $author->id,
            'title' => 'Tortitas de papa',
            'status' => 'published',
            'published_at' => now(),
        ]);

        RecipeRating::query()->create([
            'recipe_id' => $recipe->id,
            'guest_name' => 'A',
            'ip_hash' => hash('sha256', '1'),
            'stars' => 4,
        ]);

        RecipeRating::query()->create([
            'recipe_id' => $recipe->id,
            'guest_name' => 'B',
            'ip_hash' => hash('sha256', '2'),
            'stars' => 5,
        ]);

        $response = $this->getJson('/api/feed/daily-recommendation?limit=1');

        $response->assertOk();
        $response->assertJsonPath('data.0.id', $recipe->id);
    }
}
