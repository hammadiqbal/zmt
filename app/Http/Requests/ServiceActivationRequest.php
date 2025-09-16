<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceActivationRequest extends FormRequest
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
            'act_s_org' => 'required',
            'act_s_site' => 'required',
            'act_s_service' => 'required',
            'act_s_performingcc' => 'required',
            'act_s_billingcc' => 'required',
            'act_s_mode' => 'required',
            'a_service_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'act_s_org.required' => 'Please Select Organization',
            'act_s_site.required' => 'Please Select Site',
            'act_s_service.required' => 'Please Select Service',
            'act_s_billingcc.required' => 'Please Select Billing Cost Center',
            'act_s_performingcc.required' => 'Please Select Performing Cost Center',
            'act_s_mode.required' => 'Please Select Service Modes',
            'a_service_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
