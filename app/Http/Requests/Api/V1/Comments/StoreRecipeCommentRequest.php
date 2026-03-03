<?php

namespace App\Http\Requests\Api\V1\Comments;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecipeCommentRequest extends FormRequest
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
            'body' => ['required', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'integer', 'exists:recipe_comments,id'],
            'guest_name' => ['nullable', 'string', 'max:100'],
        ];
    }
}
