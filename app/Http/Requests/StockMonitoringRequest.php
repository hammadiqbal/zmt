<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockMonitoringRequest extends FormRequest
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
            'sm_org' => 'required',
            'sm_site' => 'required',
            'sm_generic' => 'required',
            'sm_location' => 'required',
            'sm_min_stock' => 'required|numeric',
            'sm_max_stock' => 'required|numeric',
            'sm_monthly_consumption' => 'required|numeric',
            'sm_min_reorder' => 'required|numeric',
            'sm_primary_email' => 'required|email',
            'sm_secondary_email' => 'required|email',
            'sm_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'sm_org.required' => 'Please select Organization',
            'sm_site.required' => 'Please select Site',
            'sm_generic.required' => 'Please select Item Generic',
            'sm_location.required' => 'Please select Service Location',
            'sm_min_stock.required' => 'Minimum stock is required.',
            'sm_min_stock.numeric' => 'Minimum stock must be a number.',
            'sm_max_stock.required' => 'Maximum stock is required.',
            'sm_max_stock.numeric' => 'Maximum stock must be a number.',
            'sm_monthly_consumption.required' => 'Monthly consumption is required.',
            'sm_monthly_consumption.numeric' => 'Monthly consumption must be a number.',
            'sm_min_reorder.required' => 'Minimum reorder is required.',
            'sm_min_reorder.numeric' => 'Minimum reorder must be a number.',
            'sm_primary_email.required' => 'Primary email field is required.',
            'sm_primary_email.email' => 'Please enter a valid email address.',
            'sm_secondary_email.required' => 'Secondary email field is required.',
            'sm_secondary_email.email' => 'Please enter a valid email address.',
            'sm_edt.required' => 'The effective date and time field is required.',
        ];
    }
}
