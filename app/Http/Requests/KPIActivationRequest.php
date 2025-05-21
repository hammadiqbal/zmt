<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KPIActivationRequest extends FormRequest
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
            'act_kpi' => 'required',
            'act_kpi_org' => 'required',
            'act_kpi_site' => 'required',
            'act_kpi_cc' => 'required',
            'a_kpi_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'act_kpi.required' => 'Please Select KPI',
            'act_kpi_org.required' => 'Please Select Organization',
            'act_kpi_site.required' => 'Please Select Site',
            'act_kpi_cc.required' => 'Please Select Cost Center',
            'a_kpi_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
