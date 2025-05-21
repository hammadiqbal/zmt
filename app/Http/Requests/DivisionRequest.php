<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DivisionRequest extends FormRequest
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
            'division' => 'required',
            'province' => 'required',
            'dedt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'division.required' => 'Please enter a Division name',
            'dedt.required' => 'Please select a division effective date & Time',
            'province.required' => 'Please select Province',
        ];
    }
}
