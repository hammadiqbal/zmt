<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KPIDimensionRequest extends FormRequest
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
            'kd_name' => "required",
            'kd_code' => "required",
            'kd_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'kd_name.required' => 'Please enter KPI Dimension',
            'kd_code.required' => 'Please enter KPI Dimension Code',
            'kd_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
