<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KPIRequest extends FormRequest
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
            'kpi_name' => 'required',
            'kpi_type' => 'required',
            'kpi_edt' => 'required',
        ];
    }
    public function messages()
    {
        return [
            'kpi_name.required' => 'Please enter KPI',
            'kpi_type.required' => 'Please select KPI Type',
            'kpi_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
