<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddUserRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // 'username' => 'required|string|regex:/^[A-Za-z\s]+$/',
            // 'useremail' => 'required|email',
            'userOrg' => 'required',
            'userRole' => 'required',
            'userEdt' => 'required',
        ];
    }


    public function messages()
    {
        return [
            // 'username.required' => 'Please enter a user name',
            // 'username.string' => 'User name should contain only alphabetic characters',
            // 'username.regex' => 'User name should contain only alphabetic characters',
            'userEdt.required' => 'Please select effective date&time for this user',
            'userOrg.required' => 'Please select Organization',
            'userRole.required' => 'Please select User Role',
            // 'useremail.required' => 'This field is required.',
            // 'useremail.email' => 'Please enter a valid email address.',
        ];
    }
}
