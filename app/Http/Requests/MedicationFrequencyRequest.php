<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MedicationFrequencyRequest extends FormRequest
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
            'medication_frequency' => 'required|string',
            'medicationfrequency_org' => 'required',
            'medicationfrequency_edt' => 'required',
        ];
    }
    public function messages()
    {
        return [
            'medication_frequency.required' => 'Please enter Medication Frequency Description',
            'medication_frequency.string' => 'Medication Frequency Description should contain only alphabetic characters',
            'medicationfrequency_org.required' => 'Please select Organization',
            'medicationfrequency_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
