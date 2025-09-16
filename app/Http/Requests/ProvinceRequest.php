<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProvinceRequest extends FormRequest
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
            'province' => 'required|string',
            'pedt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'province.required' => 'Please enter a province name',
            'province.string' => 'Province name should contain only alphabetic characters',
            'pedt.required' => 'Please select a province effective date & Time',
        ];
    }
}
