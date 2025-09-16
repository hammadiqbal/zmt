<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinancialTransactionRequest extends FormRequest
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
            'fr_org' => 'required',
            'fr_site' => 'required',
            'fr_transactiontype' => 'required',
            'fr_paymentoption' => 'required',
            'fr_amount' => 'required',
            'fr_remarks' => 'required|string',
            'fr_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'fr_org.required' => 'Please select Organization',
            'fr_site.required' => 'Please select Site',
            'fr_transactiontype.required' => 'Please select Transaction Type',
            'fr_paymentoption.required' => 'Please select Payment Option',
            'fr_amount.required' => 'Please Enter Amount',
            'fr_remarks.required' => 'Please Enter Remarks',
            'fr_remarks.string' => 'Remarks should contain only alphabetic characters',
            'fr_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
