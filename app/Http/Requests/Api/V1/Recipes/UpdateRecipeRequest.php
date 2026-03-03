<?php

namespace App\Http\Requests\Api\V1\Recipes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRecipeRequest extends FormRequest
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
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'summary' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'description' => ['sometimes', 'nullable', 'string'],
            'servings' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:999'],
            'prep_time_minutes' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:9999'],
            'cook_time_minutes' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:9999'],
            'status' => ['sometimes', Rule::in(['draft', 'published'])],
            'video_type' => ['sometimes', 'nullable', Rule::in(['upload', 'youtube'])],
            'video_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'category_ids' => ['sometimes', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'ingredients' => ['sometimes', 'array'],
            'ingredients.*.name' => ['required_with:ingredients', 'string', 'max:255'],
            'ingredients.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'ingredients.*.unit' => ['nullable', 'string', 'max:30'],
            'ingredients.*.notes' => ['nullable', 'string', 'max:255'],
            'ingredients.*.calories' => ['nullable', 'numeric', 'min:0'],
            'ingredients.*.protein_g' => ['nullable', 'numeric', 'min:0'],
            'ingredients.*.carbs_g' => ['nullable', 'numeric', 'min:0'],
            'ingredients.*.fat_g' => ['nullable', 'numeric', 'min:0'],
            'steps' => ['sometimes', 'array'],
            'steps.*.instruction' => ['required_with:steps', 'string'],
            'steps.*.image_path' => ['nullable', 'string', 'max:255'],
        ];
    }
}
