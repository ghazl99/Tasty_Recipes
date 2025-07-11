<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {


        return $this->routeIs('api.auth.register')
        ?  [
                'name'      => 'required|string|max:255',
                'email'     => 'required|string|email|max:255|unique:users',
                'password'  => [
                    'required',
                    'string',
                    Password::min(8) // Minimum length of 8 characters
                        ->mixedCase() // Must include both uppercase and lowercase letters
                        ->letters()   // Must include at least one letter
                        ->numbers()   // Must include at least one number
                        ->symbols()   // Must include at least one symbol
                        ->uncompromised(), // Checks against known data breaches
                ],
                'image'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]
        : [
            'name'      => 'sometimes|string|max:255',
            'email'     => 'sometimes|email|unique:users,email,' . $this->user()->id,
            'image'     => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors(),
        ], 422));
    }
}
