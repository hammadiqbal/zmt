<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
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
            'emp_name' => 'required|string',
            'emp_guardian_name' => 'required|string',
            'emp_guardian_relation' => 'required',
            'emp_next_of_kin' => 'required|string',
            'emp_language' => 'required|string',
            'emp_nextofkin_relation' => 'required',
            'emp_religion' => 'required',
            'emp_marital_status' => 'required',
            'emp_gender' => 'required',
            'emp_dob' => 'required',
            'emp_org' => 'required',
            'emp_site' => 'required',
            'emp_cc' => 'required',
            'emp_prefix' => 'required',
            'emp_cadre' => 'required',
            'emp_position' => 'required',
            'emp_weekHrs' => 'required|integer',
            // 'start_time' => 'required',
            // 'end_time' => 'required',
            'emp_qual_lvl' => 'required',
            'emp_status' => 'required',
            'emp_working_status' => 'required',
            'emp_doj' => 'required',
            'emp_province' => 'required',
            'emp_division' => 'required',
            'emp_district' => 'required',
            'emp_reportto' => 'required',
            'emp_cnic' => 'required|regex:/^[0-9]{13}$/',
            'cnic_expiry' => 'required',
            'emp_address' => 'required|string',
            'mailing_address' => 'required|string',
            'emp_cell' => ['required', 'regex:/^[+]?[\d-]+$/'],
            'emp_edt' => 'required',
            'emp_img' => ['required','mimes:png,jpg,webp,jpeg'],
        ];
    }

    public function messages()
    {
        return [
            'emp_name.required' => 'Please enter a Employee name',
            'emp_name.string' => 'Employee name should contain only alphabetic characters',
            'emp_guardian_name.required' => 'Please enter a Employee Father/Husband name',
            'emp_guardian_name.string' => 'Employee Father/Husband name should contain only alphabetic characters',
            'emp_guardian_relation.required' => 'Please select Employee Father/Husband Relation',
            'emp_next_of_kin.required' => 'Please enter a Employee Next Of Kin',
            'emp_next_of_kin.string' => 'Employee Next Of Kin should contain only alphabetic characters',
            'emp_nextofkin_relation.required' => 'Please select Employee Next Of Kin Relation',
            'emp_language.required' => 'Please enter a Employee Language',
            'emp_language.string' => 'Employee Language should contain only alphabetic characters',
            'emp_religion.required' => 'Please select Employee Religion',
            'emp_marital_status.required' => 'Please select Employee Marital Status',
            'emp_gender.required' => 'Please select Employee Gender',
            'emp_dob.required' => 'Please select Employee DOB',
            'emp_org.required' => 'Please select Employee Organization',
            'emp_site.required' => 'Please select Employee Site',
            'emp_cc.required' => 'Please select Employee Cost Center',
            'emp_prefix.required' => 'Please select Prefix for Employee',
            'emp_cadre.required' => 'Please select Employee Cadre',
            'emp_position.required' => 'Please select Employee Position',
            'emp_weekHrs.required' => 'Please Enter Employee Week Hours',
            'emp_weekHrs.integer' => 'This field only accept numbers',
            // 'start_time.required' => 'Please select Employee Week Hours',
            // 'end_time.required' => 'Please select Employee Week Hours',
            'emp_qual_lvl.required' => 'Please select Employee Qualification Level',
            'emp_working_status.required' => 'Please select Employee Working Status',
            'emp_doj.required' => 'Please select Employee Date of Joining',
            'emp_province.required' => 'Please select Province',
            'emp_division.required' => 'Please select Division',
            'emp_district.required' => 'Please select District',
            'emp_reportto.required' => 'Please select Employee or write any notes',
            'emp_address.required' => 'The address field is required.',
            'emp_address.string' => 'The address field must be a string.',
            'mailing_address.required' => 'The mailing address field is required.',
            'mailing_address.string' => 'The mailing address field must be a string.',
            'emp_cnic.required' => 'Please Enter Employee CNIC)',
            'emp_cnic.regex' => 'Please Enter Correct CNIC# without dashes (422xxxxxxxxxx)',
            'cnic_expiry.required' => 'Please select expiry of your CNIC.',
            'emp_cell.required' => 'The cell number field is required.',
            'emp_cell.regex' => 'The Employee cell number must be in the format: +920000000000',
            'emp_edt.required' => 'The effective date and time field is required.',
            'emp_img.required' => 'This field is required.',
            'emp_img.mimes' => 'Only PNG, JPG, WEBP, and JPEG file types are allowed for image.',
            'emp_status.required' => 'Please select Employee Status',
        ];
    }
}
