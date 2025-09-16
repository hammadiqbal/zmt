<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CCActivationRequest extends FormRequest
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
            'cc_org' => 'required',
            'cc_site' => 'required',
            'cc_name' => 'required',
            'a_cc_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'cc_org.required' => 'Please Select Organization',
            'cc_site.required' => 'Please Select Site',
            'cc_name.required' => 'Please Select Cost Center',
            'a_cc_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
