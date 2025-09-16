<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PatientRegistrationRequest extends FormRequest
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
            'patient_name' => 'required|string',
            'guardian_name' => 'required|string',
            'guardian_relation' => 'required',
            'language' => 'required|string',
            'religion' => 'required|string',
            'marital_status' => 'required|string',
            'patient_gender' => 'required|string',
            // 'patient_dob' => 'required|date',
            'patient_age' => 'required',
            'patient_org' => 'required|string',
            'patient_site' => 'required|string',
            'patient_province' => 'required|string',
            'patient_division' => 'required|string',
            'patient_district' => 'required|string',
            'patient_address' => 'required|string',
            'patient_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'patient_name.required' => 'Please enter a Patient name',
            'patient_name.string' => 'Patient name should contain only alphabetic characters',
            'guardian_name.required' => 'Please enter a Patient Guardian name',
            'guardian_name.string' => 'Patient Guardian name should contain only alphabetic characters',
            'guardian_relation.required' => "Select patient's Guardian relation",
            'relation.string' => "Patient's next of kin relation should contain only alphabetic characters",
            'language.required' => 'Please enter a Patient Language',
            'language.string' => 'Patient Language should contain only alphabetic characters',
            'religion.required' => 'Please select Patient Religion',
            'marital_status.required' => 'Please select Patient Marital Status',
            'patient_gender.required' => 'Please select Gender',
            'patient_age.required' => 'Please Enter Patient Age',
            // 'patient_dob.required' => 'Please select Patient Date of Birth',
            // 'patient_dob.date' => 'Please enter a valid date for Patient Date of Birth',
            'patient_org.required' => 'Please select Organization',
            'patient_site.required' => 'Please select Site',
            'patient_province.required' => 'Please select Province',
            'patient_division.required' => 'Please select Division',
            'patient_district.required' => 'Please select District',
            'patient_address.required' => 'The address field is required.',
            'patient_edt.required' => 'The effective date and time field is required.',
        ];
    }
}
