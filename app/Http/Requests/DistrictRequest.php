<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DistrictRequest extends FormRequest
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
            'district' => 'required',
            'division' => 'required',
            'province' => 'required',
            'dt_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'district.required' => 'Please enter a district name',
            'dt_edt.required' => 'Please select a district effective date & Time',
            'province.required' => 'Please select Province',
            'division.required' => 'Please select Division',
        ];
    }
}
