<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeLocationAllocationRequest extends FormRequest
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
            'org_ela' => 'required',
            'site_ela' => 'required',
            'emp_ela' => 'required',
            'ela_edt' => 'required',
            'invSite' => 'required',
            'location_ela' => 'required',
        ];
    }


    public function messages()
    {
        return [
            'org_ela.required' => 'Please Select Organization',
            'site_ela.required' => 'Please Select Site',
            'emp_ela.required' => 'Please Select Employee',
            'invSite.required' => 'Please Select location Site',
            'location_ela.required' => 'Please Select Inventory Location',
            'ela_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
