<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCategoryRequest extends FormRequest
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
            'name' => 'required|string|min:3|max:50|unique:categories,name',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name is required. Please provide it.',
            'name.string' => 'The name must be a string.',
            'name.min' => 'The name must be at least 3 characters long.',
            'name.max' => 'The name must not exceed 50 characters.',
            'name.unique' => 'The name has already been taken. Please choose another.',
            'image.required' => 'An image is required. Please upload one.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be of type: jpeg, png, jpg, gif.',
            'image.max' => 'The image must not exceed 2MB.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => $validator->errors()
        ], 422));
    }
}
