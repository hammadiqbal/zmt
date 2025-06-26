<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OtherTransactionRequest extends FormRequest
{
 /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'ot_org' => 'required|exists:organization,id',
            'ot_site' => 'required|exists:org_site,id',
            'ot_transactiontype' => 'required|exists:inventory_transaction_type,id',
            // 'ot_source' => 'required',
            // 'ot_destination' => 'required',
            'ot_source' => 'nullable',
            'ot_destination' => 'nullable',
            'ot_generic' => 'required|array',
            'ot_generic.*' => 'required|exists:inventory_generic,id',
            'ot_brand' => 'required|array',
            'ot_brand.*' => 'required|exists:inventory_brand,id',
            'ot_batch' => 'required|array',
            'ot_batch.*' => 'required|string',
            'ot_expiry' => 'required|array',
            'ot_expiry.*' => 'required|date',
            'ot_qty' => 'required|array',
            'ot_qty.*' => 'required|numeric|min:1',
            'ot_reference_document' => 'nullable|string|max:255',
            'ot_remarks' => 'nullable|string|max:1000',
            'ot_demand_qty.*' => 'nullable|numeric',
            'ot_performing_cc.*' => 'nullable',

        ];
        if ($this->input('source_applicable') == '1') {
            $rules['ot_source'] = 'required';
        }
        if ($this->input('destination_applicable') == '1') {
            $rules['ot_destination'] = 'required';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'ot_org.required' => 'Organization is required',
            'ot_org.exists' => 'Selected organization is invalid',
            'ot_site.required' => 'Site is required',
            'ot_site.exists' => 'Selected site is invalid',
            'ot_transactiontype.required' => 'Transaction Type is required',
            'ot_transactiontype.exists' => 'Selected transaction type is invalid',
            'ot_source.required' => 'Source is required',
            'ot_destination.required' => 'Destination is required',
            'ot_generic.required' => 'At least one item generic is required',
            'ot_generic.*.required' => 'Item Generic is required',
            'ot_generic.*.exists' => 'Selected item generic is invalid',
            'ot_brand.required' => 'At least one brand is required',
            'ot_brand.*.required' => 'Item Brand is required',
            'ot_brand.*.exists' => 'Selected brand is invalid',
            'ot_batch.*.required' => 'Batch Number is required',
            'ot_expiry.*.required' => 'Expiry Date is required',
            'ot_expiry.*.date' => 'Invalid Expiry Date format',
            'ot_qty.*.required' => 'Transaction Quantity is required',
            'ot_qty.*.numeric' => 'Transaction Quantity must be a number',
            'ot_qty.*.min' => 'Transaction Quantity must be greater than 0',
            'ot_performing_cc.required' => 'Performing Cost Center is required when MR is provided',
            'ot_physician.required' => 'Physician is required when MR is provided'
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Format expiry dates to Y-m-d format
        if ($this->has('ot_expiry')) {
            $expiry_dates = collect($this->ot_expiry)->map(function ($date) {
                return date('Y-m-d', strtotime($date));
            })->toArray();
            $this->merge(['ot_expiry' => $expiry_dates]);
        }
    }
}
