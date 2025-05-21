<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequisitionRequest extends FormRequest
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
            'sr_org' => 'required',
            'sr_service' => 'required',
            'sr_status' => 'required',
            'sr_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'sr_org.required' => 'Please Select Organization',
            'sr_service.required' => 'Please Select a Service',
            'sr_status.required' => 'This field is required',
            'sr_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
