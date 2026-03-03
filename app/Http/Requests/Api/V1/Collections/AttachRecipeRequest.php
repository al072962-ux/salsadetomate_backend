<?php

namespace App\Http\Requests\Api\V1\Collections;

use Illuminate\Foundation\Http\FormRequest;

class AttachRecipeRequest extends FormRequest
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
            'recipe_id' => ['required', 'integer', 'exists:recipes,id'],
        ];
    }
}
