<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Collection as RecipeCollection;
use App\Models\Follow;
use App\Models\Recipe;
use App\Models\RecipeComment;
use App\Models\RecipeIngredient;
use App\Models\RecipeLike;
use App\Models\RecipeMedia;
use App\Models\RecipeRating;
use App\Models\RecipeStep;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class DemoDataSeeder extends Seeder
{
    /**
     * Categorias demo globales (minimo 15).
     * Modifica este arreglo para personalizar categorias.
     *
     * @var array<int, string>
     */
    private array $globalCategoryNames = [
        'Desayunos',
        'Comida mexicana',
        'Sopas y caldos',
        'Salsas',
        'Ensaladas',
        'Pasta',
        'Arroz y granos',
        'Pollo',
        'Carnes',
        'Pescados y mariscos',
        'Postres caseros',
        'Bebidas',
        'Antojitos',
        'Vegetarianas',
        'Económicas',
        'Para 1 persona',
        'Para adultos mayores',
        'Sin horno',
    ];

    /**
     * Plantillas de colecciones demo.
     *
     * @var array<int, string>
     */
    private array $collectionTemplates = [
        'Mis favoritas',
        'Recetas fáciles',
        'Para 1 persona',
        'Económicas',
    ];

    /**
     * Titulos de recetas demo.
     *
     * @var array<int, string>
     */
    private array $recipeTitles = [
        'Salsa de tomate casera',
        'Arroz rojo de la abuela',
        'Caldo de pollo tradicional',
        'Ensalada fresca de nopales',
        'Tortitas de papa doradas',
        'Pasta cremosa con ajo',
        'Sopa de lentejas rápida',
        'Pollo al limón en sartén',
        'Picadillo sencillo',
        'Pescado al horno con verduras',
        'Avena con canela y manzana',
        'Molletes gratinados',
        'Agua fresca de jamaica',
        'Flan de vainilla fácil',
        'Chilaquiles verdes suaves',
        'Frijoles de la olla',
        'Tinga de pollo clásica',
        'Crema de calabaza',
        'Tostadas de atún',
        'Pan francés casero',
        'Sopa de fideo con tomate',
        'Albóndigas en chipotle',
        'Puré de papa cremoso',
        'Atole de avena',
        'Papas al horno con romero',
        'Quesadillas de flor de calabaza',
        'Ceviche de pescado rápido',
        'Pechuga rellena de queso',
        'Tamal de elote',
        'Enchiladas rojas caseras',
    ];

    /**
     * Ingredientes base para variaciones demo.
     *
     * @var array<int, string>
     */
    private array $ingredientPool = [
        'Tomate',
        'Cebolla',
        'Ajo',
        'Cilantro',
        'Pimienta',
        'Sal',
        'Aceite vegetal',
        'Pollo',
        'Carne molida',
        'Pescado blanco',
        'Papa',
        'Zanahoria',
        'Calabaza',
        'Arroz',
        'Lentejas',
        'Crema',
        'Queso fresco',
        'Tortilla de maíz',
        'Chile guajillo',
        'Leche',
        'Avena',
        'Limón',
        'Jamaica',
        'Vainilla',
    ];

    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('DemoDataSeeder no debe ejecutarse en producción.');
        }

        $this->resetDemoTables();

        $faker = fake('es_MX');

        $admin = User::query()->create([
            'name' => 'Admin Salsa',
            'email' => 'admin@salsadetomate.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'bio' => 'Administrador de la plataforma (cuenta demo).',
            'avatar_url' => 'https://i.pravatar.cc/300?img=8',
        ]);

        $members = User::factory()->count(14)->create()->each(function (User $user, int $index): void {
            $user->update([
                'bio' => 'Cuenta demo para pruebas de la API.',
                'avatar_url' => 'https://i.pravatar.cc/300?img='.(20 + $index),
            ]);
        });

        $users = collect([$admin])->merge($members)->values();

        $globalCategories = $this->createGlobalCategories();
        $userCategories = $this->createUserCategories($users);
        $allCategories = $globalCategories->merge($userCategories)->values();

        $recipes = $this->createRecipes($users, $allCategories);
        $publishedRecipes = $recipes->where('status', 'published')->values();

        $this->createRatings($publishedRecipes, $users);
        $this->createComments($publishedRecipes, $users);
        $this->createLikes($publishedRecipes, $users);
        $this->createFollows($users);
        $this->createCollections($users, $publishedRecipes);

        $this->command?->info('DemoDataSeeder: datos de prueba insertados en tablas del dominio.');
    }

    private function resetDemoTables(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::table('category_recipe')->truncate();
        DB::table('collection_recipes')->truncate();
        DB::table('follows')->truncate();
        DB::table('recipe_likes')->truncate();
        DB::table('recipe_comments')->truncate();
        DB::table('recipe_ratings')->truncate();
        DB::table('recipe_media')->truncate();
        DB::table('recipe_steps')->truncate();
        DB::table('recipe_ingredients')->truncate();
        DB::table('collections')->truncate();
        DB::table('categories')->truncate();
        DB::table('recipes')->truncate();
        DB::table('users')->truncate();

        Schema::enableForeignKeyConstraints();
    }

    /**
     * @return \Illuminate\Support\Collection<int, Category>
     */
    private function createGlobalCategories()
    {
        return collect($this->globalCategoryNames)->map(function (string $name): Category {
            return Category::query()->create([
                'user_id' => null,
                'name' => $name,
                'slug' => Str::slug($name),
                'is_global' => true,
            ]);
        });
    }

    /**
     * @param \Illuminate\Support\Collection<int, User> $users
     * @return \Illuminate\Support\Collection<int, Category>
     */
    private function createUserCategories($users)
    {
        return $users->where('role', 'member')->take(6)->flatMap(function (User $user) {
            $names = [
                'Recetas de '.$user->name,
                'Favoritas de fin de semana',
            ];

            return collect($names)->map(function (string $name) use ($user): Category {
                return Category::query()->create([
                    'user_id' => $user->id,
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'is_global' => false,
                ]);
            });
        })->values();
    }

    /**
     * @param \Illuminate\Support\Collection<int, User> $users
     * @param \Illuminate\Support\Collection<int, Category> $allCategories
     * @return \Illuminate\Support\Collection<int, Recipe>
     */
    private function createRecipes($users, $allCategories)
    {
        $faker = fake('es_MX');
        $recipes = collect();
        $titleIndex = 0;

        foreach ($users as $user) {
            for ($i = 0; $i < 2; $i++) {
                $title = $this->recipeTitles[$titleIndex] ?? ($faker->sentence(3).' '.$titleIndex);
                $titleIndex++;

                $status = $faker->boolean(78) ? 'published' : 'draft';
                $prep = $faker->numberBetween(5, 25);
                $cook = $faker->numberBetween(10, 60);

                $recipe = Recipe::query()->create([
                    'user_id' => $user->id,
                    'title' => $title,
                    'summary' => $faker->sentence(10),
                    'description' => $faker->paragraph(3),
                    'servings' => $faker->numberBetween(1, 8),
                    'prep_time_minutes' => $prep,
                    'cook_time_minutes' => $cook,
                    'status' => $status,
                    'published_at' => $status === 'published' ? Carbon::now()->subDays($faker->numberBetween(1, 120)) : null,
                    'video_type' => $faker->boolean(35) ? 'youtube' : null,
                    'video_url' => $faker->boolean(35) ? 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' : null,
                ]);

                $recipe->categories()->sync(
                    $allCategories->shuffle()->take($faker->numberBetween(2, 4))->pluck('id')->all()
                );

                $nutrition = [
                    'calories' => 0.0,
                    'protein_g' => 0.0,
                    'carbs_g' => 0.0,
                    'fat_g' => 0.0,
                ];

                $ingredientNames = collect($faker->randomElements($this->ingredientPool, $faker->numberBetween(4, 7)))->values();

                foreach ($ingredientNames as $position => $ingredientName) {
                    $ingredient = RecipeIngredient::query()->create([
                        'recipe_id' => $recipe->id,
                        'position' => $position + 1,
                        'name' => $ingredientName,
                        'quantity' => $faker->randomFloat(2, 0.25, 5.00),
                        'unit' => $faker->randomElement(['pieza', 'taza', 'cda', 'g', 'ml']),
                        'notes' => $faker->boolean(25) ? $faker->sentence(4) : null,
                        'calories' => $faker->randomFloat(2, 10, 180),
                        'protein_g' => $faker->randomFloat(2, 0, 18),
                        'carbs_g' => $faker->randomFloat(2, 0, 30),
                        'fat_g' => $faker->randomFloat(2, 0, 20),
                    ]);

                    $nutrition['calories'] += (float) $ingredient->calories;
                    $nutrition['protein_g'] += (float) $ingredient->protein_g;
                    $nutrition['carbs_g'] += (float) $ingredient->carbs_g;
                    $nutrition['fat_g'] += (float) $ingredient->fat_g;
                }

                $stepTotal = $faker->numberBetween(3, 6);
                for ($step = 1; $step <= $stepTotal; $step++) {
                    RecipeStep::query()->create([
                        'recipe_id' => $recipe->id,
                        'step_number' => $step,
                        'instruction' => 'Paso '.$step.': '.$faker->sentence(10),
                        'image_path' => $faker->boolean(30) ? "seed/recipes/{$recipe->id}/step-{$step}.jpg" : null,
                    ]);
                }

                $mainImage = RecipeMedia::query()->create([
                    'recipe_id' => $recipe->id,
                    'type' => 'image',
                    'disk' => 'public',
                    'path' => "seed/recipes/{$recipe->id}/main.jpg",
                    'mime_type' => 'image/jpeg',
                    'size_bytes' => $faker->numberBetween(120_000, 900_000),
                    'is_primary' => true,
                    'sort_order' => 0,
                ]);

                RecipeMedia::query()->create([
                    'recipe_id' => $recipe->id,
                    'type' => 'image',
                    'disk' => 'public',
                    'path' => "seed/recipes/{$recipe->id}/gallery-1.jpg",
                    'mime_type' => 'image/jpeg',
                    'size_bytes' => $faker->numberBetween(120_000, 900_000),
                    'is_primary' => false,
                    'sort_order' => 1,
                ]);

                if ($faker->boolean(40)) {
                    RecipeMedia::query()->create([
                        'recipe_id' => $recipe->id,
                        'type' => 'video',
                        'disk' => 'public',
                        'external_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                        'mime_type' => 'video/youtube',
                        'size_bytes' => null,
                        'is_primary' => false,
                        'sort_order' => 2,
                    ]);
                }

                $recipe->update([
                    'main_image_id' => $mainImage->id,
                    'nutrition' => [
                        'calories' => round($nutrition['calories'], 2),
                        'protein_g' => round($nutrition['protein_g'], 2),
                        'carbs_g' => round($nutrition['carbs_g'], 2),
                        'fat_g' => round($nutrition['fat_g'], 2),
                    ],
                ]);

                $recipes->push($recipe->fresh());
            }
        }

        return $recipes;
    }

    /**
     * @param \Illuminate\Support\Collection<int, Recipe> $publishedRecipes
     * @param \Illuminate\Support\Collection<int, User> $users
     */
    private function createRatings($publishedRecipes, $users): void
    {
        $faker = fake('es_MX');

        foreach ($publishedRecipes as $recipe) {
            $eligibleUsers = $users->where('id', '!=', $recipe->user_id)->shuffle()->values();
            $userRatings = $eligibleUsers->take($faker->numberBetween(3, 6));

            foreach ($userRatings as $user) {
                RecipeRating::query()->create([
                    'recipe_id' => $recipe->id,
                    'user_id' => $user->id,
                    'stars' => $faker->numberBetween(3, 5),
                    'review' => $faker->sentence(12),
                    'turned_out_well' => $faker->boolean(90),
                    'changes' => $faker->boolean(35) ? $faker->sentence(8) : null,
                    'would_cook_again' => $faker->boolean(85),
                ]);
            }

            for ($guest = 1; $guest <= 2; $guest++) {
                RecipeRating::query()->create([
                    'recipe_id' => $recipe->id,
                    'guest_name' => 'Invitado '.$guest,
                    'guest_email' => "invitado{$guest}+r{$recipe->id}@demo.test",
                    'ip_hash' => hash('sha256', "seed-guest-{$recipe->id}-{$guest}"),
                    'stars' => $faker->numberBetween(4, 5),
                    'review' => $faker->sentence(10),
                    'turned_out_well' => true,
                    'changes' => null,
                    'would_cook_again' => true,
                ]);
            }
        }
    }

    /**
     * @param \Illuminate\Support\Collection<int, Recipe> $publishedRecipes
     * @param \Illuminate\Support\Collection<int, User> $users
     */
    private function createComments($publishedRecipes, $users): void
    {
        $faker = fake('es_MX');

        foreach ($publishedRecipes as $recipe) {
            $commentUsers = $users->where('id', '!=', $recipe->user_id)->shuffle()->take(2)->values();

            if ($commentUsers->count() < 2) {
                continue;
            }

            $firstComment = RecipeComment::query()->create([
                'recipe_id' => $recipe->id,
                'user_id' => $commentUsers[0]->id,
                'parent_id' => null,
                'body' => $faker->sentence(14),
            ]);

            RecipeComment::query()->create([
                'recipe_id' => $recipe->id,
                'user_id' => $commentUsers[1]->id,
                'parent_id' => null,
                'body' => $faker->sentence(12),
            ]);

            RecipeComment::query()->create([
                'recipe_id' => $recipe->id,
                'user_id' => $recipe->user_id,
                'parent_id' => $firstComment->id,
                'body' => 'Gracias por tu comentario, me da gusto que te sirviera.',
            ]);
        }
    }

    /**
     * @param \Illuminate\Support\Collection<int, Recipe> $publishedRecipes
     * @param \Illuminate\Support\Collection<int, User> $users
     */
    private function createLikes($publishedRecipes, $users): void
    {
        $faker = fake('es_MX');

        foreach ($publishedRecipes as $recipe) {
            $likeUsers = $users->where('id', '!=', $recipe->user_id)->shuffle()->take($faker->numberBetween(2, 6));

            foreach ($likeUsers as $user) {
                RecipeLike::query()->firstOrCreate([
                    'recipe_id' => $recipe->id,
                    'user_id' => $user->id,
                ]);
            }
        }
    }

    /**
     * @param \Illuminate\Support\Collection<int, User> $users
     */
    private function createFollows($users): void
    {
        $userCount = $users->count();

        if ($userCount < 3) {
            return;
        }

        foreach ($users->values() as $index => $user) {
            for ($offset = 1; $offset <= 2; $offset++) {
                $target = $users[($index + $offset) % $userCount];

                if ((int) $target->id === (int) $user->id) {
                    continue;
                }

                Follow::query()->firstOrCreate([
                    'follower_id' => $user->id,
                    'followed_id' => $target->id,
                ]);
            }
        }
    }

    /**
     * @param \Illuminate\Support\Collection<int, User> $users
     * @param \Illuminate\Support\Collection<int, Recipe> $publishedRecipes
     */
    private function createCollections($users, $publishedRecipes): void
    {
        $faker = fake('es_MX');
        $recipeIds = $publishedRecipes->pluck('id');

        foreach ($users as $user) {
            foreach ($this->collectionTemplates as $template) {
                $collection = RecipeCollection::query()->create([
                    'user_id' => $user->id,
                    'name' => $template,
                    'description' => 'Colección demo: '.$template,
                    'is_system' => $template === 'Mis favoritas',
                ]);

                $take = min($recipeIds->count(), $faker->numberBetween(3, 8));
                $selected = $recipeIds->shuffle()->take($take)->all();

                $collection->recipes()->syncWithoutDetaching($selected);
            }
        }
    }
}
