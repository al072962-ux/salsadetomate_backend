<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Categories\StoreCategoryRequest;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user('api');

        $categories = Category::query()
            ->when(
                $user,
                fn ($query) => $query->where('is_global', true)->orWhere('user_id', $user->id),
                fn ($query) => $query->where('is_global', true)
            )
            ->with('user')
            ->orderByDesc('is_global')
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request): CategoryResource
    {
        $user = $request->user('api');
        $data = $request->validated();

        $isGlobal = $user->isAdmin() && (bool) ($data['is_global'] ?? false);
        $ownerId = $isGlobal ? null : $user->id;

        $baseSlug = Str::slug($data['name']);
        $baseSlug = $baseSlug !== '' ? $baseSlug : Str::lower(Str::random(8));
        $slug = $baseSlug;
        $suffix = 2;

        while (Category::query()->where('user_id', $ownerId)->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        $category = Category::query()->create([
            'user_id' => $ownerId,
            'name' => $data['name'],
            'slug' => $slug,
            'is_global' => $isGlobal,
        ]);

        return CategoryResource::make($category->load('user'));
    }
}
