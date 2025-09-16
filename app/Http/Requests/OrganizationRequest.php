<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrganizationRequest extends FormRequest
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
            'org_name' => 'required',
            'org_person_name' => 'required|string',
            'org_code' => 'required|size:4',
            'org_remarks' => 'required|string|max:150',
            'org_address' => 'required|string',
            'org_province' => 'required',
            'org_division' => 'required',
            'org_district' => 'required',
            'org_person_email' => 'required|email',
            'org_website' => 'required|url',
            // 'org_gps' => ['nullable', 'regex:/^[-]?(\d{1,2})\.\d{6}, [-]?(\d{1,3})\.\d{6}$/'],
            'org_cell' => ['required', 'regex:/^[+]?[\d-]+$/'],
            'org_landline' => ['required', 'regex:/^[+]?[\d-]+$/'],
            'org_edt' => 'required',
            'org_logo' => ['required', 'mimes:png,jpg,webp,jpeg'],
            // 'org_logo' => ['required', 'dimensions:width=500,height=250', 'mimes:png,jpg,webp,jpeg'],
            // 'org_banner' => ['required', 'dimensions:width=1000,height=200', 'mimes:png,jpg,webp,jpeg'],
            'org_banner' => ['required', 'mimes:png,jpg,webp,jpeg'],
        ];
    }

    public function messages()
    {
        return [
            'org_name.required' => 'Please enter a Organization name',
            'org_person_name.required' => 'Please enter a focal person name',
            'org_person_name.string' => 'Focal person name should contain only alphabetic characters',
            'org_edt.required' => 'Please select a Organization effective date & Time',
            'org_remarks.required' => 'The remarks field is required.',
            'org_remarks.string' => 'The remarks field must be a string.',
            'org_remarks.max' => 'The remarks field may not exceed 150 characters.',
            'org_address.required' => 'The address field is required.',
            'org_address.string' => 'The address field must be a string.',
            'org_code.required' => 'Please enter a Organization code',
            'org_code.size' => 'Organization Code must be 4 characters long.',
            'org_province.required' => 'Please select Province',
            'org_division.required' => 'Please select Division',
            'org_district.required' => 'Please select District',
            'org_person_email.required' => 'The focal person email field is required.',
            'org_person_email.email' => 'Please enter a valid email address for the focal person.',
            'org_website.required' => 'The website URL field is required.',
            'org_website.url' => 'Please enter a valid website URL.',
            // 'org_gps.regex' => 'Please enter valid GPS coordinates in the format "latitude, longitude".',
            'org_cell.required' => 'The cell number field is required.',
            'org_cell.regex' => 'The organization cell number must be in the format: +920000000000',
            'org_landline.required' => 'The landline number field is required.',
            'org_landline.regex' => 'The organization landline number must be in the format: +922100000000',
            'org_edt.required' => 'The effective date and time field is required.',
            'org_logo.required' => 'The logo field is required.',
            //'org_logo.dimensions' => 'The logo must have dimensions of 500x250 pixels.',
            'org_logo.mimes' => 'Only PNG, JPG, WEBP, and JPEG file types are allowed for the logo.',
            'org_banner.required' => 'The banner field is required.',
            //'org_banner.dimensions' => 'The banner must have dimensions of 1000x200 pixels.',
            'org_banner.mimes' => 'Only PNGs, JPG, WEBP, and JPEG file types are allowed for the banner.',
        ];
    }
}
