<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinancialPayrollDeductionRequest extends FormRequest
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
            'payrolldeduction' => 'required|string',
            'pd_org' => 'required',
            'pd_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'payrolldeduction.required' => 'Please enter Financial Payroll Deduction',
            'pd_org.required' => 'Please select Organization',
            'pd_edt.required' => 'The effective date and time field is required.',
        ];
    }
}
