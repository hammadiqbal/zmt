<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KPIGroupRequest extends FormRequest
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
            'kg_name' => 'required',
            'kg_code' => 'required',
            'kg_edt' => 'required',
        ];
    }
    public function messages()
    {
        return [
            'kg_code.required' => 'Please enter KPI Group Code',
            'kg_name.required' => 'Please enter KPI Group',
            'kg_edt.required' => 'Please select Effective Date&Time',
        ];
    }


}
