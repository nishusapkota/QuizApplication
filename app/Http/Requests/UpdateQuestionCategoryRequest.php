<?php

namespace App\Http\Requests;

use App\Rules\ValidSlug;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionCategoryRequest extends FormRequest
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
        $questionCategoryId = $this->route()->originalParameter('question_category');
        return [
            'title' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                new ValidSlug,
                "unique:question_categories,slug,{$questionCategoryId},id,deleted_at,NULL"
            ],
        ];
    }

}
