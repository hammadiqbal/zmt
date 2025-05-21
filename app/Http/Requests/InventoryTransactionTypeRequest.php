<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryTransactionTypeRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'itt_org' => 'required',
            'description' => 'required|string',
            'activity_type' => 'required',
            'request_mandatory' => 'required',
            'request_location_mandatory' => 'required',
            'source_location_type' => 'required',
            'source_action' => 'required',
            'destination_location_type' => 'required',
            'destination_action' => 'required',
            'inventory_location' => 'required',
            'applicable_location' => 'required',
            'transaction_expired_status' => 'required',
            'itt_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'itt_org.required' => 'Please select Organization',
            'description.required' => 'Please enter a Inventory Transaction Type Description',
            'description.string' => 'Inventory Transaction Type Description should contain only alphabetic characters',
            'activity_type.required' => 'Please select Activity Type',
            'request_mandatory.required' => 'Please select Request Mandatory Status',
            'request_location_mandatory.required' => 'Please select Request Location Mandatory Status',
            'source_location_type.required' => 'Please select Source Type',
            'source_action.required' => 'Please select Source Action',
            'destination_location_type.required' => 'Please select Destination Location Type',
            'destination_action.required' => 'Please select Destination Transaction Action',
            'inventory_location.required' => 'Please select Controlled / Alloted Inventory Locations',
            'applicable_location.required' => 'Please select Applicable Location',
            'transaction_expired_status.required' => 'Please select Transaction Expired Status',
            'itt_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
