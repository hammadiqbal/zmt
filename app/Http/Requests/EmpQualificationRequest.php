<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmpQualificationRequest extends FormRequest
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
            'eq-org' => 'required',
            'eq-site' => 'required',
            'qualification' => 'required',
            'emp-id' => 'required',
            'emp-ql.*' => 'required',
            'ql_date.*' => 'required',
        ];
    }


    public function messages()
    {
        return [
            'eq-org.required' => 'Please Select Organization',
            'eq-site.required' => 'Please Select Site',
            'qualification.required' => 'Please enter a Employee Qualification Description',
            'emp-id.required' => 'Please select Employee',
            'emp-ql.required' => 'Please select Qualifcation Level',
            'ql_date.required' => 'Please select Date of Qualification',

        ];
    }
}
