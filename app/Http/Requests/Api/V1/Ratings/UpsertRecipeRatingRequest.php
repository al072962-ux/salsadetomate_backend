<?php

namespace App\Http\Requests\Api\V1\Ratings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertRecipeRatingRequest extends FormRequest
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
            'stars' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['nullable', 'string', 'max:2000'],
            'guest_name' => [
                Rule::requiredIf(fn (): bool => auth('api')->guest()),
                'nullable',
                'string',
                'max:120',
            ],
            'guest_email' => ['nullable', 'email', 'max:255'],
            'turned_out_well' => ['nullable', 'boolean'],
            'changes' => ['nullable', 'string', 'max:2000'],
            'would_cook_again' => ['nullable', 'boolean'],
        ];
    }
}
