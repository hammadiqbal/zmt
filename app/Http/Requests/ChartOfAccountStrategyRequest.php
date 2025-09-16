<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChartOfAccountStrategyRequest extends FormRequest
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
            'accountStrategy' => 'required',
            'as_remarks' => 'required',
            'as_level' => 'required',
            'as_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'accountStrategy.required' => 'Please enter Strategy Description',
            'as_remarks.required' => 'Strategy Remarks should contain only alphabetic characters',
            'as_level.required' => 'Please Select Account Hierarchy Level',
            'as_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
