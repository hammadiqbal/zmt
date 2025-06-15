<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReferralSiteRequest extends FormRequest
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
            'rf_org' => 'required',
            'rf_desc' => 'required',
            'rf_province' => 'required',
            'rf_division' => 'required',
            'rf_district' => 'required',
            'rf_edt' => 'required',
        ];
    }


    public function messages()
    {
        return [
            'rf_org.required' => 'This field is required',
            'rf_desc.required' => 'This field is required',
            'rf_province.required' => 'This field is required',
            'rf_division.required' => 'This field is required',
            'rf_district.required' => 'This field is required',
            'rf_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
