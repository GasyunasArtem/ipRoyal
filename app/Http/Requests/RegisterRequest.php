<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxNameLength = config('business.validation.max_name_length');
        $minPasswordLength = config('business.validation.min_password_length');
        $maxPasswordLength = config('business.validation.max_password_length');
        
        return [
            'name' => "required|string|max:{$maxNameLength}|regex:/^[a-zA-Z\s]+$/",
            'email' => "required|string|email:rfc|max:{$maxNameLength}|unique:users",
            'password' => [
                'required',
                'string',
                "min:{$minPasswordLength}",
                "max:{$maxPasswordLength}",
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            ],
            'country' => 'nullable|string|size:2|regex:/^[A-Z]{2}$/',
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Name may only contain letters and spaces.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one digit and one special character.',
            'country.regex' => 'Country must be a valid 2-letter ISO code in uppercase.',
        ];
    }
}
