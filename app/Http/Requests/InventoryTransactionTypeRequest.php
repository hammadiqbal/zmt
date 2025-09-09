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
            'request_emp_location' => 'required',
            'source_location_type' => 'required',
            'source_action' => 'required',
            'destination_location_type' => 'required',
            'destination_action' => 'required',
            'source_locations_value' => 'required',
            'destination_locations_value' => 'required',
            'emp_location_check' => 'required',
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
            'request_emp_location.required' => 'Please select status for employee location validation for requisition',
            'source_location_type.required' => 'Please select Source Type',
            'source_action.required' => 'Please select Source Action',
            'destination_location_type.required' => 'Please select Destination Location Type',
            'destination_action.required' => 'Please select Destination Transaction Action',
            'source_locations_value.required' => 'Please select Controlled / Alloted Source Locations',
            'destination_locations_value.required' => 'Please select Controlled / Alloted Destination Locations',
            'emp_location_check.required' => 'Please select employee location status for soruce/destination',
            'transaction_expired_status.required' => 'Please select Transaction Expired Status',
            'itt_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
