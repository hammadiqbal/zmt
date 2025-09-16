<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReturnRequest extends FormRequest
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
            'return_org' => 'required|exists:organization,id',
            'return_site' => 'required|exists:org_site,id',
            'return_transactiontype' => 'required|exists:inventory_transaction_type,id',
            'return_performing_cc' => 'required',
            'return_generic' => 'required',
            'return_source' => 'nullable',
            'return_destination' => 'nullable',
            'return_brand' => 'required',
            'return_batch' => 'required',
            'return_expiry' => 'required',
            'return_qty' => 'required',
            'return_reference_document' => 'nullable|string|max:255',
            'return_remarks' => 'nullable|string|max:1000',
        ];
        if ($this->input('source_applicable') == '1') {
            $rules['return_source'] = 'required';
        }
        if ($this->input('destination_applicable') == '1') {
            $rules['return_destination'] = 'required';
        }

        if ($this->filled('return_mr')) {
            $rules['return_mr'] = 'exists:patient,mr_code';
            $rules['return_service'] = 'required|exists:services,id';
            $rules['return_servicemode'] = 'required|exists:service_mode,id';
            $rules['return_billingcc'] = 'required|exists:costcenter,id';
            $rules['return_physician'] = 'required|exists:employee,id';
            $rules['return_duration'] = 'required';
            $rules['return_frequency'] = 'required';
            $rules['return_route'] = 'required';
            $rules['return_dose'] = 'required';
            $rules['return_demand_qty'] = 'nullable';
        } else {
            $rules['return_mr'] = 'nullable';
            $rules['return_service'] = 'nullable';
            $rules['return_servicemode'] = 'nullable';
            $rules['return_billingcc'] = 'nullable';
            $rules['return_physician'] = 'nullable';
            $rules['return_duration'] = 'nullable';
            $rules['return_frequency'] = 'nullable';
            $rules['return_route'] = 'nullable';
            $rules['return_dose'] = 'nullable';
            $rules['return_demand_qty'] = 'required';
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
            'return_org.required' => 'Organization is required',
            'return_org.exists' => 'Selected organization is invalid',
            'return_site.required' => 'Site is required',
            'return_site.exists' => 'Selected site is invalid',
            'return_transactiontype.required' => 'Transaction Type is required',
            'return_transactiontype.exists' => 'Selected transaction type is invalid',
            'return_source.required' => 'Source is required',
            'return_destination.required' => 'Destination is required',
            'return_generic.required' => 'At least one item generic is required',
            'return_brand.required' => 'At least one brand is required',
            'return_batch.required' => 'Batch Number is required',
            'return_expiry.required' => 'Expiry Date is required',
            'return_qty.required' => 'Transaction Quantity is required',
            'return_service.required' => 'Service is required when MR is provided',
            'return_servicemode.required' => 'Service Mode is required when MR is provided',
            'return_billingcc.required' => 'Billing Cost Center is required when MR is provided',
            'return_performing_cc.required' => 'Performing Cost Center is required when MR is provided',
            'return_physician.required' => 'Physician is required when MR is provided',
            'return_duration.required' => 'This field is required',
            'return_frequency.required' => 'This field is required',
            'return_dose.required' => 'This field is required',
            'return_route.required' => 'This field is required',
            'return_demand_qty.required' => 'This field is required',
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
        if ($this->has('return_expiry')) {
            $expiry_dates = collect($this->return_expiry)->map(function ($date) {
                return date('Y-m-d', strtotime($date));
            })->toArray();
            $this->merge(['return_expiry' => $expiry_dates]);
        }
    }
}
