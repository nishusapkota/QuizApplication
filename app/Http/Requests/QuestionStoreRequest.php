<?php

namespace App\Http\Requests;

use App\Rules\ValidSlug;
use Illuminate\Foundation\Http\FormRequest;

class QuestionStoreRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => "string|required|max:255",
            'category_id' => "required|exists:question_categories,id",
            'slug' => [
                'required',
                'string',
                'max:255',
                "unique:questions,slug,NULL,id,deleted_at,NULL",
                new ValidSlug,
            ],
            'description' => "string|nullable|max:5000",
            'options' => "array|required",
            'answer' => "string|required|in_array:options.*",
            "weightage" => "required|integer|in:5,10,15",
            "status" => "boolean|nullable"
        ];
    }
}
