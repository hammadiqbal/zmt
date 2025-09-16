<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConsumptionGroupRequest extends FormRequest
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
            'cg_org' => 'required',
            'cg_desc' => 'required',
            'cg_remarks' => 'required',
            'cg_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'cg_org.required' => 'Please Select an organization',
            'cg_desc.required' => 'Please enter Description',
            'cg_remarks.required' => 'Please select Remarks',
            'cg_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
