<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinancialPaymentRequest extends FormRequest
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
            'fp_org' => 'required',
            'fp_site' => 'required',
            'fp_transactiontype' => 'required',
            'fp_paymentoption' => 'required',
            'fp_amount' => 'required',
            'fp_remarks' => 'required|string',
            'fp_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'fp_org.required' => 'Please select Organization',
            'fp_site.required' => 'Please select Site',
            'fp_transactiontype.required' => 'Please select Transaction Type',
            'fp_paymentoption.required' => 'Please select Payment Option',
            'fp_amount.required' => 'Please Enter Amount',
            'fp_remarks.required' => 'Please Enter Remarks',
            'fp_remarks.string' => 'Remarks should contain only alphabetic characters',
            'fp_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
