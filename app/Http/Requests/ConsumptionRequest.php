<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConsumptionRequest extends FormRequest
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
            'consumption_org' => 'required|exists:organization,id',
            'consumption_site' => 'required|exists:org_site,id',
            'consumption_transactiontype' => 'required|exists:inventory_transaction_type,id',
            'consumption_performing_cc' => 'required',
            'consumption_generic' => 'required',
            'consumption_source' => 'nullable',
            'consumption_destination' => 'nullable',
            // 'consumption_generic.*' => 'required|exists:inventory_generic,id',
            'consumption_brand' => 'required',
            // 'consumption_brand.*' => 'required|exists:inventory_brand,id',
            'consumption_batch' => 'required',
            // 'consumption_batch.*' => 'required|string',
            'consumption_expiry' => 'required',
            // 'consumption_expiry.*' => 'required|date',
            'consumption_qty' => 'required',
            // 'consumption_qty.*' => 'required|numeric|min:1',
            'consumption_reference_document' => 'nullable|string|max:255',
            'consumption_remarks' => 'nullable|string|max:1000',
        ];
        if ($this->input('source_applicable') == '1') {
            $rules['consumption_source'] = 'required';
        }
        if ($this->input('destination_applicable') == '1') {
            $rules['consumption_destination'] = 'required';
        }

        if ($this->filled('consumption_mr')) {
            $rules['consumption_mr'] = 'exists:patient,mr_code';
            $rules['consumption_service'] = 'required|exists:services,id';
            $rules['consumption_servicemode'] = 'required|exists:service_mode,id';
            $rules['consumption_billingcc'] = 'required|exists:costcenter,id';
            $rules['consumption_physician'] = 'required|exists:employee,id';
            $rules['consumption_duration'] = 'required';
            $rules['consumption_frequency'] = 'required';
            $rules['consumption_route'] = 'required';
            $rules['consumption_dose'] = 'required';
            $rules['consumption_demand_qty'] = 'nullable';
        } else {
            $rules['consumption_mr'] = 'nullable';
            $rules['consumption_service'] = 'nullable';
            $rules['consumption_servicemode'] = 'nullable';
            $rules['consumption_billingcc'] = 'nullable';
            $rules['consumption_physician'] = 'nullable';
            $rules['consumption_duration'] = 'nullable';
            $rules['consumption_frequency'] = 'nullable';
            $rules['consumption_route'] = 'nullable';
            $rules['consumption_dose'] = 'nullable';
            $rules['consumption_demand_qty'] = 'required';
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
            'consumption_org.required' => 'Organization is required',
            'consumption_org.exists' => 'Selected organization is invalid',
            'consumption_site.required' => 'Site is required',
            'consumption_site.exists' => 'Selected site is invalid',
            'consumption_transactiontype.required' => 'Transaction Type is required',
            'consumption_transactiontype.exists' => 'Selected transaction type is invalid',
            'consumption_source.required' => 'Source is required',
            'consumption_destination.required' => 'Destination is required',
            'consumption_generic.required' => 'At least one item generic is required',
            // 'consumption_generic.*.required' => 'Item Generic is required',
            // 'consumption_generic.*.exists' => 'Selected item generic is invalid',
            'consumption_brand.required' => 'At least one brand is required',
            // 'consumption_brand.*.required' => 'Item Brand is required',
            // 'consumption_brand.*.exists' => 'Selected brand is invalid',
            'consumption_batch.required' => 'Batch Number is required',
            'consumption_expiry.required' => 'Expiry Date is required',
            // 'consumption_expiry.*.date' => 'Invalid Expiry Date format',
            'consumption_qty.required' => 'Transaction Quantity is required',
            // 'consumption_qty.*.numeric' => 'Transaction Quantity must be a number',
            // 'consumption_qty.*.min' => 'Transaction Quantity must be greater than 0',
            'consumption_service.required' => 'Service is required when MR is provided',
            'consumption_servicemode.required' => 'Service Mode is required when MR is provided',
            'consumption_billingcc.required' => 'Billing Cost Center is required when MR is provided',
            'consumption_performing_cc.required' => 'Performing Cost Center is required when MR is provided',
            'consumption_physician.required' => 'Physician is required when MR is provided',
            'consumption_duration.required' => 'This field is required',
            'consumption_frequency.required' => 'This field is required',
            'consumption_dose.required' => 'This field is required',
            'consumption_route.required' => 'This field is required',
            'consumption_demand_qty.required' => 'This field is required',
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
        if ($this->has('consumption_expiry')) {
            $expiry_dates = collect($this->consumption_expiry)->map(function ($date) {
                return date('Y-m-d', strtotime($date));
            })->toArray();
            $this->merge(['consumption_expiry' => $expiry_dates]);
        }
    }
}
