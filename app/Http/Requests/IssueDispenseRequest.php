<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IssueDispenseRequest extends FormRequest
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
            'id_org' => 'required|exists:organization,id',
            'id_site' => 'required|exists:org_site,id',
            'id_transactiontype' => 'required|exists:inventory_transaction_type,id',
            'id_source' => 'required',
            'id_destination' => 'required',
            'id_generic' => 'required|array',
            'id_generic.*' => 'required|exists:inventory_generic,id',
            'id_brand' => 'required|array',
            'id_brand.*' => 'required|exists:inventory_brand,id',
            'id_batch' => 'required|array',
            'id_batch.*' => 'required|string',
            'id_expiry' => 'required|array',
            'id_expiry.*' => 'required|date',
            'id_qty' => 'required|array',
            'id_qty.*' => 'required|numeric|min:1',
            'id_reference_document' => 'nullable|string|max:255',
            'id_remarks' => 'nullable|string|max:1000',
            'id_demand_qty.*' => 'nullable|numeric',
        ];

        // Add conditional validation for MR related fields
        $sourceType = $this->input('source_type');

        if ($sourceType === 'material') {
            // For material source
            if ($this->filled('id_mr')) {
                // If MR is provided, validate it exists but keep service fields optional
                $rules['id_mr'] = 'exists:patient,mr_code';
                $rules['id_service'] = 'nullable|exists:services,id';
                $rules['id_servicemode'] = 'nullable|exists:service_mode,id';
                $rules['id_billingcc'] = 'nullable|exists:costcenter,id';
                $rules['id_performing_cc'] = 'nullable';
                $rules['id_physician'] = 'nullable|exists:employee,id';
            } else {
                // If no MR, all service-related fields are optional
                $rules['id_mr'] = 'nullable';
                $rules['id_service'] = 'nullable';
                $rules['id_servicemode'] = 'nullable';
                $rules['id_billingcc'] = 'nullable';
                $rules['id_performing_cc'] = 'nullable';
                $rules['id_physician'] = 'nullable';
            }
        } else {
            // For non-material source (medication)
            if ($this->filled('id_mr')) {
                // If MR exists, require all service fields
                $rules['id_mr'] = 'exists:patient,mr_code';
                $rules['id_service'] = 'required|exists:services,id';
                $rules['id_servicemode'] = 'required|exists:service_mode,id';
                $rules['id_billingcc'] = 'required|exists:costcenter,id';
                $rules['id_performing_cc'] = 'required';
                $rules['id_physician'] = 'required|exists:employee,id';
            } else {
                // If no MR, make fields optional
                $rules['id_mr'] = 'nullable';
                $rules['id_service'] = 'nullable';
                $rules['id_servicemode'] = 'nullable';
                $rules['id_billingcc'] = 'nullable';
                $rules['id_performing_cc'] = 'nullable';
                $rules['id_physician'] = 'nullable';
            }
        }
        // if ($this->filled('id_mr')) {
        //     $rules['id_mr'] = 'exists:patient,mr_code';
            
        //     // If MR exists, service becomes required
        //     $rules['id_service'] = 'required|exists:services,id';
        //     $rules['id_servicemode'] = 'required|exists:service_mode,id';
        //     $rules['id_billingcc'] = 'required|exists:costcenter,id';
        //     $rules['id_performing_cc'] = 'required';
        //     $rules['id_physician'] = 'required|exists:employee,id';
        // } else {
        //     // If no MR, these fields should be nullable
        //     $rules['id_service'] = 'nullable|exists:services,id';
        //     $rules['id_servicemode'] = 'nullable|exists:service_mode,id';
        //     $rules['id_billingcc'] = 'nullable|exists:costcenter,id';
        //     $rules['id_performing_cc'] = 'nullable';
        //     $rules['id_physician'] = 'nullable|exists:employee,id';
        // }

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
            'id_org.required' => 'Organization is required',
            'id_org.exists' => 'Selected organization is invalid',
            'id_site.required' => 'Site is required',
            'id_site.exists' => 'Selected site is invalid',
            'id_transactiontype.required' => 'Transaction Type is required',
            'id_transactiontype.exists' => 'Selected transaction type is invalid',
            'id_source.required' => 'Source is required',
            'id_destination.required' => 'Destination is required',
            'id_generic.required' => 'At least one item generic is required',
            'id_generic.*.required' => 'Item Generic is required',
            'id_generic.*.exists' => 'Selected item generic is invalid',
            'id_brand.required' => 'At least one brand is required',
            'id_brand.*.required' => 'Item Brand is required',
            'id_brand.*.exists' => 'Selected brand is invalid',
            'id_batch.*.required' => 'Batch Number is required',
            'id_expiry.*.required' => 'Expiry Date is required',
            'id_expiry.*.date' => 'Invalid Expiry Date format',
            'id_qty.*.required' => 'Transaction Quantity is required',
            'id_qty.*.numeric' => 'Transaction Quantity must be a number',
            'id_qty.*.min' => 'Transaction Quantity must be greater than 0',
            'id_service.required' => 'Service is required when MR is provided',
            'id_servicemode.required' => 'Service Mode is required when MR is provided',
            'id_billingcc.required' => 'Billing Cost Center is required when MR is provided',
            'id_performing_cc.required' => 'Performing Cost Center is required when MR is provided',
            'id_physician.required' => 'Physician is required when MR is provided'
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
        if ($this->has('id_expiry')) {
            $expiry_dates = collect($this->id_expiry)->map(function ($date) {
                return date('Y-m-d', strtotime($date));
            })->toArray();
            $this->merge(['id_expiry' => $expiry_dates]);
        }
    }
}