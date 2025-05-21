<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ICDCodingRequest extends FormRequest
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
            'icd_desc' => 'required',
            'icd_code' => 'required',
            'icd_codetype' => 'required',
            'icd_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'icd_desc.required' => 'Please Enter ICD  Description',
            'icd_code.required' => 'Please Enter ICD Code',
            'icd_codetype.required' => 'Please Enter ICD Code Type',
            'icd_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
