<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VendorRegistrationRequest extends FormRequest
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
            'vendor_desc' => 'required|string',
            'vendor_org' => 'required',
            'vendor_address' => 'required|string',
            'vendor_name' => 'required|string',
            'vendor_email' => 'required|email',
            'vendor_cell' => ['required', 'regex:/^[+]?[\d-]+$/'],
            'vendor_remarks' => 'required|string',
            'vendor_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'vendor_desc.required' => 'Please enter Vendor Description',
            'vendor_desc.string' => 'Vendor Description should contain only alphabetic characters',
            'vendor_org.required' => 'Please select Organization',
            'vendor_address.required' => 'The address field is required.',
            'vendor_address.string' => 'The address field must be a string.',
            'vendor_name.required' => 'Please enter Focal Person Name',
            'vendor_name.string' => 'Focal Person Name should contain only alphabetic characters',
            'vendor_email.required' => 'This field is required.',
            'vendor_email.email' => 'Please enter a valid email address.',
            'vendor_cell.required' => 'The cell number field is required.',
            'vendor_cell.regex' => 'The Vendor cell number must be in the format: +920000000000',
            'vendor_remarks.required' => 'Please enter Remarks for Vendor',
            'vendor_remarks.string' => 'This field should contain only alphabetic characters',
            'vendor_edt.required' => 'The effective date and time field is required.',
        ];
    }
}
