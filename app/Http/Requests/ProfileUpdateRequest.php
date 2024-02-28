<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->profile->user_id === auth()->id();
    }

    /**
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->skills && is_string($this->skills)) {
            $this->merge([
                'skills' => explode(',', $this->skills),
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
        return [
            "skills" => "required|array",
            'education' => 'required|string|max:5000',
            'experience' => 'required|string|max:5000',
            'career' => 'required|string|max:5000',
            'avatar' => 'image|mimes:jpg,png,jpeg|max:2048',
        ];
    }
}
