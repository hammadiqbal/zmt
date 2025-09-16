<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CostCenterTypeRequest extends FormRequest
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
            'cc_type' => 'required',
            'cc_remarks' => 'required|max:150',
            'ordering_cc' => 'required',
            'cct_edt' => 'required',
            'performing_cc' => 'required',
        ];
    }


    public function messages()
    {
        return [
            'cc_type.required' => 'Please enter a Cost Center Type',
            'cc_remarks.required' => 'The remarks field is required.',
            'cc_remarks.max' => 'The remarks field may not exceed 150 characters.',
            'performing_cc.required' => 'Please select Performing CC Status',
            'ordering_cc.required' => 'Please select Ordering CC Status ',
            'cct_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
