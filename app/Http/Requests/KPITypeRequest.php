<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KPITypeRequest extends FormRequest
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
            'kt_name' => 'required',
            'kt_code' => 'required',
            'kt_group' => 'required',
            'kt_dimension' => 'required',
            'kt_edt' => 'required',
        ];
    }
    public function messages()
    {
        return [
            'kt_name.required' => 'Please enter KPI Type',
            'kt_code.required' => 'Please enter KPI Type Code',
            'kt_group.required' => 'Please select KPI Group',
            'kt_dimension.required' => 'Please select KPI Group',
            'kt_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
