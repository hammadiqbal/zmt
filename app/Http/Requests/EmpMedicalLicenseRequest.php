<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmpMedicalLicenseRequest extends FormRequest
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
            'em-org' => 'required',
            'em-site' => 'required',
            'emp-id' => 'required',
            'medicalLicense.*' => 'required',
            'ref_no.*' => 'required',
            'expire_date.*' => 'required',
        ];
    }


    public function messages()
    {
        return [
            'em-org.required' => 'Please Select Organization',
            'em-site.required' => 'Please Select Site',
            'emp-id.required' => 'Please select Employee',
            'medicalLicense.required' => 'Please Enter Medical License Description',
            'ref_no.required' => 'Please Enter License Ref No.',
            'expire_date.required' => 'Please select Expiry Date',
        ];
    }
}
