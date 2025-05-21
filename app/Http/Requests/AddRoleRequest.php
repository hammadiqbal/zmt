<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddRoleRequest extends FormRequest
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
            'role_name' => 'required',
            'role_edt' => 'required',
            'role_remarks' => 'required|string|max:150',
        ];
    }

    public function messages()
    {
        return [
            'role_name.required' => 'Please enter a role name',
            'role_edt.required' => 'Please select a role effective date & Time',
            'role_remarks.required' => 'Please enter remarks for role',
            'role_remarks.max' => 'Remarks should not exceed 150 characters',
        ];
    }
}
