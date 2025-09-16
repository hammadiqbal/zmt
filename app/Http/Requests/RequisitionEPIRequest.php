<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequisitionEPIRequest extends FormRequest
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
            'repi_org' => 'required',
            'repi_site' => 'required',
            'sevice_id' => 'required',
            'servicemode_id' => 'required',
            'billingcc_id' => 'required',
            // 'repi_remarks' => 'required',
            // 'repi_edt' => 'required',
        ];
        if (in_array($this->input('action'), ['i', 'p'])) {
            $rules['physician'] = 'required';
        }
    }

    public function messages()
    {
        return [
            'repi_org.required' => 'This field is required',
            'repi_site.required' => 'This field is required',
            'sevice_id.required' => 'This field is required',
            'servicemode_id.required' => 'This field is required',
            'physician.required' => 'This field is required',
            'billingcc_id.required' => 'This field is required',
            // 'repi_remarks.required' => 'This field is required',
            // 'repi_edt.required' => 'This field is required',
        ];
    }
}
