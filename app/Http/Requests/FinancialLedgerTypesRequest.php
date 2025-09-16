<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinancialLedgerTypesRequest extends FormRequest
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
            'ledgertype' => 'required|string',
            'flt_org' => 'required',
            'flt_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'ledgertype.required' => 'Please enter Financial Ledger Type',
            'flt_org.required' => 'Please select Organization',
            'flt_edt.required' => 'The effective date and time field is required.',
        ];
    }
}
