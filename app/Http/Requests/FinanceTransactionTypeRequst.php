<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinanceTransactionTypeRequst extends FormRequest
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
            'ftt_desc' => 'required|string',
            'ftt_org' => 'required',
            'ftt_activitytype' => 'required',
            'ftt_source' => 'required',
            'ftt_destination' => 'required',
            'ftt_debit' => 'required',
            'ftt_credit' => 'required',
            'ftt_ledger' => 'required',
            'ftt_amounteditable' => 'required',
            'ftt_discountallowed' => 'required',
            'ftt_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'ftt_desc.required' => 'Please enter a Inventory Category Description',
            'ftt_desc.string' => 'Inventory Category Description should contain only alphabetic characters',
            'ftt_org.required' => 'Please select Organization',
            'ftt_activitytype.required' => 'Please select Activity Type',
            'ftt_source.required' => 'Please select Transaction Source',
            'ftt_destination.required' => 'Please select Transaction Destination',
            'ftt_debit.required' => 'Please select Debit Account',
            'ftt_credit.required' => 'Please select Credit Account',
            'ftt_ledger.required' => 'Please select Ledger Type',
            'ftt_amounteditable.required' => 'Please select Amount Editing Status',
            'ftt_discountallowed.required' => 'Please select Discount Status',
            'ftt_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
