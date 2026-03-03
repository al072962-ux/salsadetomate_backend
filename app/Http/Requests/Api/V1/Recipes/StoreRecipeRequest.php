<?php

namespace App\Http\Requests\Api\V1\Recipes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'servings' => ['nullable', 'integer', 'min:1', 'max:999'],
            'prep_time_minutes' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'cook_time_minutes' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'status' => ['nullable', Rule::in(['draft', 'published'])],
            'video_type' => ['nullable', Rule::in(['upload', 'youtube'])],
            'video_url' => ['nullable', 'url', 'max:2048'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'ingredients' => ['nullable', 'array'],
            'ingredients.*.name' => ['required_with:ingredients', 'string', 'max:255'],
            'ingredients.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'ingredients.*.unit' => ['nullable', 'string', 'max:30'],
            'ingredients.*.notes' => ['nullable', 'string', 'max:255'],
            'ingredients.*.calories' => ['nullable', 'numeric', 'min:0'],
            'ingredients.*.protein_g' => ['nullable', 'numeric', 'min:0'],
            'ingredients.*.carbs_g' => ['nullable', 'numeric', 'min:0'],
            'ingredients.*.fat_g' => ['nullable', 'numeric', 'min:0'],
            'steps' => ['nullable', 'array'],
            'steps.*.instruction' => ['required_with:steps', 'string'],
            'steps.*.image_path' => ['nullable', 'string', 'max:255'],
        ];
    }
}
