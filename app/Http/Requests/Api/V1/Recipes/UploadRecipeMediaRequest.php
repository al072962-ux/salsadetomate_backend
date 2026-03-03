<?php

namespace App\Http\Requests\Api\V1\Recipes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadRecipeMediaRequest extends FormRequest
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
            'file' => [
                'required_without:external_url',
                'file',
                'max:'.((string) config('recipes.max_media_upload_kb', 51200)),
            ],
            'external_url' => ['required_without:file', 'url', 'max:2048'],
            'type' => ['nullable', Rule::in(['image', 'video'])],
            'is_primary' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
