<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ThirdPartyRegistrationRequest extends FormRequest
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
            'tp_org' => 'required',
            'registration_type' => 'required',
            'vendor_cat' => 'required',
            'tp_name' => 'required',
            'tp_email' => 'required|email',
            'tp_cell' => ['required', 'regex:/^[+]?[\d-]+$/'],
            'tp_address' => 'required|string',
            'tp_prefix' => 'required',
            'tp_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'tp_org.required' => 'Please select Organization',
            'registration_type.required' => 'Please select Registration Type',
            'vendor_cat.required' => 'Please select Vendor Category',
            'tp_name.required' => 'Please enter a Focal Person name',
            'tp_email.required' => 'Focal Person email field is required.',
            'tp_email.email' => 'Please enter a valid email address.',
            'tp_cell.required' => 'The cell number field is required.',
            'tp_cell.regex' => 'The cell number must be in the format: +920000000000',
            'tp_address.string' => 'This field should contain only alphabetic characters.',
            'tp_prefix.required' => 'This field is required.',
            'tp_edt.required' => 'The effective date and time field is required.',
        ];
    }
}
