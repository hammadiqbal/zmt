<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConsumptionMethodRequest extends FormRequest
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
            'cm_org' => 'required',
            'cm_desc' => 'required',
            'cm_criteria' => 'required',
            'cm_group' => 'required',
            'cm_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'cm_org.required' => 'Please Select an organization',
            'cm_desc.required' => 'Please enter Description',
            'cm_criteria.required' => 'Please enter Criteria',
            'cm_group.required' => 'Please select Consumption Group',
            'cm_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
