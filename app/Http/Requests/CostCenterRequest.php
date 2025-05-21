<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CostCenterRequest extends FormRequest
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
            'cost_center' => 'required',
            'cc_code' => 'required',
            'cc_type' => 'required',
            'cc_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'cost_center.required' => 'Please enter a Cost Center',
            'cc_code.required' => 'Please enter Cost Center code',
            'cc_type.required' => 'Please select Cost Center Type',
            'cc_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
