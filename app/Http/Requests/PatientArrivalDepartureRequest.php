<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PatientArrivalDepartureRequest extends FormRequest
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
            'pio_mr' => 'required|string',
            'pio_org' => 'required',
            'pio_site' => 'required',
            'pio_status' => 'required',
            'pio_priority' => 'required',
            'pio_serviceLocation' => 'required',
            'pio_serviceSchedule' => 'required',
            'pio_emp' => 'required',
            'pio_service' => 'required',
            'pio_serviceMode' => 'required',
            'pio_billingCC' => 'required',
            // 'amount_received' => 'required|string|regex:/^[0-9]+$/',
            'pio_payMode' => 'required',
            // 'pio_serviceStart' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'pio_mr.required' => 'Please enter MR#',
            'pio_mr.string' => 'Ensure that the MR# consists of only alphabets and numbers.',
            'pio_org.required' => 'Please select Organization',
            'pio_site.required' => 'Please select Site',
            'pio_status.required' => 'Please enter Patient Status',
            'pio_priority.required' => 'Please enter Patient Priority',
            'pio_serviceLocation.required' => "Please Select Service Location",
            'pio_serviceSchedule.required' => "Please Select Service Schedule",
            'pio_emp.required' => 'Please Select Designated Physician',
            'pio_service.string' => 'Patient Select Service',
            'pio_serviceMode.required' => 'Please Select Service Mode',
            'pio_billingCC.required' => 'Please Select Billing Cost Center',
            'patient_dob.required' => 'Please select Patient Date of Birth',
            // 'amount_received.required' => 'This field is required',
            'pio_payMode.required' => 'Please select Payment Mode',
            // 'pio_serviceStart.required' => 'The Service start date and time field is required.',
        ];
    }
}
