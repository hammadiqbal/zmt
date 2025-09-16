<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmpServiceAllocationRequest extends FormRequest
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
            'emp_sa' => 'required',
            'org_sa' => 'required',
            'site_sa' => 'required',
            'service_sa' => 'required',
            'sa_edt' => 'required',
        ];
    }


    public function messages()
    {
        return [
            'emp_sa.required' => 'Please Select Employee',
            'org_sa.required' => 'Please Select Organization',
            'site_sa.required' => 'Please Select Site',
            'service_sa.required' => 'Please Select Service',
            'sa_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
