<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmpQualificationLevelRequest extends FormRequest
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
            'empQualification' => 'required',
            'eql_edt' => 'required',
        ];
    }


    public function messages()
    {
        return [
            'empQualification.required' => 'Please enter a Employee Qualification Level',
            'eql_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
