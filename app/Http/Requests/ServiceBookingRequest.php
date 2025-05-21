<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceBookingRequest extends FormRequest
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
            'sb_org' => 'required',
            'sb_site' => 'required',
            'sb_location' => 'required',
            'sb_schedule' => 'required',
            'sb_emp' => 'required',
            'sb_service' => 'required',
            'sb_serviceMode' => 'required',
            'sb_billingCC' => 'required',
            'sb_mr' => 'required',
            'sbp_status' => 'required',
            'sbp_priority' => 'required',
            'sb_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'sb_org.required' => 'Please select Organization',
            'sb_site.required' => 'Please select Site',
            'sb_location.required' => 'Please select Service locatiion',
            'sb_schedule.required' => 'Please select Service Location Schedule',
            'sb_emp.required' => 'Please select Designated Physician',
            'sb_service.required' => 'Please select Service',
            'sb_serviceMode.required' => 'Please select Service Mode',
            'sb_billingCC.required' => 'Please select Billing CostCenter',
            'sb_mr.required' => 'Please Enter MRI Code',
            'sbp_status.required' => 'Please select Patient Status',
            'sbp_priority.required' => 'Please select Patient Priority Status',
            'sb_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
