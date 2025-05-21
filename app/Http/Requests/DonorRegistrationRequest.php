<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DonorRegistrationRequest extends FormRequest
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
            'donor_org' => 'required',
            'donor_type' => 'required',
            'donor_name' => 'required',
            'donor_email' => 'required|email',
            'donor_cell' => ['required', 'regex:/^[+]?[\d-]+$/'],
            'donor_edt' => 'required',
            'donor_address' => 'required|string',
            'donor_remarks' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'donor_org.required' => 'Please select Donor Organization',
            'donor_type.required' => 'Please select Donor Type',
            'donor_name.required' => 'Please enter a Focal Person name',
            'donor_email.required' => 'Focal Person email field is required.',
            'donor_email.email' => 'Please enter a valid email address.',
            'donor_cell.required' => 'The cell number field is required.',
            'donor_cell.regex' => 'The cell number must be in the format: +920000000000',
            'donor_edt.required' => 'The effective date and time field is required.',
            'donor_address.required' => 'This field is required.',
            'donor_address.string' => 'This field should contain only alphabetic characters.',
            'donor_remarks.required' => 'This field is required.',
        ];
    }
}
