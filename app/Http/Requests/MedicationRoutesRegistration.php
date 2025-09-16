<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MedicationRoutesRegistration extends FormRequest
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
            'medication_route' => 'required|string',
            'medicationroute_org' => 'required',
            'medicationroute_edt' => 'required',
        ];
    }
    public function messages()
    {
        return [
            'medication_route.required' => 'Please enter Medication Route Description',
            'medication_route.string' => 'Medication Route Description should contain only alphabetic characters',
            'medicationroute_org.required' => 'Please select Organization',
            'medicationroute_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
