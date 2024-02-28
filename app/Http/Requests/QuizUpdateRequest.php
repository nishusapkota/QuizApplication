<?php

namespace App\Http\Requests;

use App\Rules\ValidSlug;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class QuizUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->question_categories && is_string($this->question_categories)) {
            $this->merge([
                'question_categories' => explode(',', $this->question_categories),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $quizId = $this->route()->originalParameter('quiz');
        return [
            'title' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                new ValidSlug,
                "unique:quizzes,slug,{$quizId},id,deleted_at,NULL",
            ],
            'category_id' => 'nullable|exists:quiz_categories,id,deleted_at,NULL',
            'thumbnail' => 'image|mimes:jpg,png,jpeg|max:2048',
            'description' => 'required|string|max:1000',
            'time' => 'required|integer|min:1',
            'retry_after' => 'required|integer|min:1',
            'pass_percentage' => 'required|integer|min:1|max:100',
            'status' => 'boolean',
            'question_categories' => 'required|array',
            'question_categories.*' => 'required|exists:question_categories,id'
        ];
    }
}
