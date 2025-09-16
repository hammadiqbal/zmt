<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmpSalaryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'es-org' => 'required',
            'es-site' => 'required',
            'emp-id' => 'required',
            // 'empSalary' => 'required|numeric',
            'salary_edt' => 'required',
        ];
    }


    public function messages()
    {
        return [
            'es-org.required' => 'Please Select Organization',
            'es-site.required' => 'Please Select Site',
            'emp-id.required' => 'Please Select Employee',
            // 'empSalary.required' => 'Please Enter Salary',
            'empSalary.numeric' => 'This fiels only accept numbers',
            'salary_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
