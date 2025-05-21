<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SiteRequest extends FormRequest
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
            'site_name' => 'required|string',
            'site_person_name' => 'required|string',
            'site_org' => 'required',
            // 'site_remarks' => 'required|string|max:150',
            'site_address' => 'required|string',
            'site_province' => 'required',
            'site_division' => 'required',
            'site_district' => 'required',
            'site_person_email' => 'required|email',
            'site_website' => 'required|url',
            'site_cell' => ['required', 'regex:/^[+]?[\d-]+$/'],
            'site_landline' => ['required', 'regex:/^[+]?[\d-]+$/'],
            'site_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'site_name.required' => 'Please enter a Site name',
            'site_name.string' => 'Site name should contain only alphabetic characters',
            'site_person_name.required' => 'Please enter a site admin name',
            'site_person_name.string' => 'Site admin name should contain only alphabetic characters',
            'site_org.required' => 'Please select Site',
            'org_edt.required' => 'Please select a Site effective date & Time',
            // 'site_remarks.required' => 'The remarks field is required.',
            // 'site_remarks.string' => 'The remarks field must be a string.',
            // 'site_remarks.max' => 'The remarks field may not exceed 150 characters.',
            'site_address.required' => 'The address field is required.',
            'site_address.string' => 'The address field must be a string.',
            'site_province.required' => 'Please select Province',
            'site_division.required' => 'Please select Division',
            'site_district.required' => 'Please select District',
            'site_person_email.required' => 'The email field is required.',
            'site_person_email.email' => 'Please enter a valid email address.',
            'site_website.required' => 'The website URL field is required.',
            'site_website.url' => 'Please enter a valid website URL.',
            'site_cell.required' => 'The cell number field is required.',
            'site_cell.regex' => 'The Site cell number must be in the format: +920000000000',
            'site_landline.required' => 'The landline number field is required.',
            'site_landline.regex' => 'The Site landline number must be in the format: +922100000000',
            'site_edt.required' => 'The effective date and time field is required.',
        ];
    }
}
