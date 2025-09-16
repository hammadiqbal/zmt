<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenderRequest extends FormRequest
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
            'gender_name' => 'required|string',
            'eg_edt' => 'required',
        ];
    }


    public function messages()
    {
        return [
            'gender_name.required' => 'Please enter a Gender',
            'gender_name.string' => 'Gender should contain only alphabetic characters',
            'eg_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
