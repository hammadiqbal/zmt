<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrefixSetupRequest extends FormRequest
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
            'prefix_name' => 'required',
            'prefix_edt' => 'required'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'prefix_name.required' => 'This field is required',
            'prefix_edt.required' => 'This field is required',
        ];
    }
} 