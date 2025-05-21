<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequisitionMedicationConsumptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rmc_transaction_type' => 'required',
            'rmc_inv_location' => 'required',
            'rmc_inv_generic' => 'required',
            'rmc_dose' => 'required',
            'rmc_route' => 'required',
            'rmc_frequency' => 'required',
            'rmc_days' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'rmc_transaction_type.required' => 'Please Select Transaction Type',
            'rmc_inv_location.required' => 'Please Select Inventory Location',
            'rmc_inv_generic.required' => 'Please Select Inventory Generic',
            'rmc_dose.required' => 'Please Enter Dose',
            'rmc_route.required' => 'Please Enter Route',
            'rmc_frequency.required' => 'Please Enter Frequency',
            'rmc_days.required' => 'Please Enter Days',
        ];
    }
}
