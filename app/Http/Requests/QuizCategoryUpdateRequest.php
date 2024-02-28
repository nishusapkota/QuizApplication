<?php

namespace App\Http\Requests;

use App\Rules\ValidSlug;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class QuizCategoryUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $quizCategoryId = $this->route()->originalParameter('quiz_category');
        return [
            'title' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                "unique:quiz_categories,slug,{$quizCategoryId},id,deleted_at,NULL",
                new ValidSlug,
            ],
        ];
    }
}
