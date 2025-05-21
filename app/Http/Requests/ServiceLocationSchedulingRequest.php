<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceLocationSchedulingRequest extends FormRequest
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
            'service_schedule' => 'required|string',
            'ss_org' => 'required',
            'ss_site' => 'required',
            'ss_location' => 'required',
            'schedule_datetime' => 'required',
            'ss_pattern' => 'required',
            'ss_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'service_schedule.required' => 'Please enter a Service Schedule Description',
            'service_schedule.string' => 'Service Schedule Description should contain only alphabetic characters',
            'ss_org.required' => 'Please select Organization',
            'ss_site.required' => 'Please select Site',
            'ss_location.required' => 'Please select Service Location',
            'schedule_datetime.required' => 'Please select Schedule Date&Time',
            'ss_pattern.required' => 'Please select Schedule Pattern',
            'ss_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
