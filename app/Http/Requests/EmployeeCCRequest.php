<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeCCRequest extends FormRequest
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
            'emp-id' => 'required',
            // 'empcc_org.*' => 'required',
            // 'empcc_site.*' => 'required',
            'empcc_org' => 'required',
            'empcc_site' => 'required',
            'emp_costcenter.*' => 'required',
            'cc_percent.*' => 'required|integer|lte:100',
            'empCC-ed' => 'required',
        ];
    }


    public function messages()
    {
        return [
            'emp-id.required' => 'Please select Employee',
            'empcc_org.required' => 'Please select Organization',
            'empcc_site.required' => 'Please select Site',
            'emp_costcenter.required' => 'Please select Cost Center',
            'cc_percent.required' => 'Please enter Cost Center Percentage',
            'empCC-ed.required' => 'Please select Effective Date&Time',
        ];
    }
}
