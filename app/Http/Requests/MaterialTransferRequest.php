<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MaterialTransferRequest extends FormRequest
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
            'mt_org' => 'required|exists:organization,id',
            // 'mt_site' => 'required|exists:org_site,id',
            'mt_transactiontype' => 'required|exists:inventory_transaction_type,id',
            // 'mt_source' => 'required',
            // 'mt_destination' => 'required',
            'mt_source_site' => 'nullable',
            'mt_source_location' => 'nullable',
            'mt_destination_site' => 'nullable',
            'mt_destination_location' => 'nullable',
            'mt_generic' => 'required|array',
            'mt_generic.*' => 'required|exists:inventory_generic,id',
            'mt_brand' => 'required|array',
            'mt_brand.*' => 'required|exists:inventory_brand,id',
            'mt_batch' => 'required|array',
            'mt_batch.*' => 'required|string',
            'mt_expiry' => 'required|array',
            'mt_expiry.*' => 'required|date',
            'mt_qty' => 'required|array',
            'mt_qty.*' => 'required|numeric|min:1',
            'mt_reference_document' => 'nullable|string|max:255',
            'mt_remarks' => 'nullable|string|max:1000',
            'mt_demand_qty.*' => 'nullable|numeric',
            'mt_performing_cc.*' => 'nullable',

        ];
        if ($this->input('source_applicable') == '1') {
            $rules['mt_source_site'] = 'required';
            $rules['mt_source_location'] = 'required';
        }
        if ($this->input('destination_applicable') == '1') {
            $rules['mt_destination_site'] = 'required';
            $rules['mt_destination_location'] = 'required';
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
            'mt_org.required' => 'Organization is required',
            'mt_org.exists' => 'Selected organization is invalid',
            'mt_site.required' => 'Site is required',
            'mt_site.exists' => 'Selected site is invalid',
            'mt_transactiontype.required' => 'Transaction Type is required',
            'mt_transactiontype.exists' => 'Selected transaction type is invalid',
            'mt_source_site.required' => 'Source site is required',
            'mt_source_location.required' => 'Source location is required',
            'mt_destination_site.required' => 'Destination site is required',
            'mt_destination_location.required' => 'Destination location is required',
            'mt_generic.required' => 'At least one item generic is required',
            'mt_generic.*.required' => 'Item Generic is required',
            'mt_generic.*.exists' => 'Selected item generic is invalid',
            'mt_brand.required' => 'At least one brand is required',
            'mt_brand.*.required' => 'Item Brand is required',
            'mt_brand.*.exists' => 'Selected brand is invalid',
            'mt_batch.*.required' => 'Batch Number is required',
            'mt_expiry.*.required' => 'Expiry Date is required',
            'mt_expiry.*.date' => 'Invalid Expiry Date format',
            'mt_qty.*.required' => 'Transaction Quantity is required',
            'mt_qty.*.numeric' => 'Transaction Quantity must be a number',
            'mt_qty.*.min' => 'Transaction Quantity must be greater than 0',
            'mt_performing_cc.required' => 'Performing Cost Center is required when MR is provided',
            'mt_physician.required' => 'Physician is required when MR is provided'
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
        if ($this->has('mt_expiry')) {
            $expiry_dates = collect($this->mt_expiry)->map(function ($date) {
                return date('Y-m-d', strtotime($date));
            })->toArray();
            $this->merge(['mt_expiry' => $expiry_dates]);
        }
    }
}
