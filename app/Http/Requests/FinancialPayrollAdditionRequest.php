<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinancialPayrollAdditionRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payrolladdition' => 'required|string',
            'pa_org' => 'required',
            'pa_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'payrolladdition.required' => 'Please enter Financial Payroll Addition',
            'pa_org.required' => 'Please select Organization',
            'pa_edt.required' => 'The effective date and time field is required.',
        ];
    }
}
