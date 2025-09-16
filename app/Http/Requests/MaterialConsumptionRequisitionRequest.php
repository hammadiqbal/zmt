<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MaterialConsumptionRequisitionRequest extends FormRequest
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
            'mc_org' => 'required',
            'mc_site' => 'required',
            'mc_transactiontype' => 'required',
            'mc_source_location' => 'nullable',
            'mc_destination_location' => 'nullable',
            'mc_itemgeneric' => 'required',
            'mc_qty' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'mc_org.required' => 'Please select Organization',
            'mc_site.required' => 'Please select Site',
            'mc_transactiontype.required' => 'Please select Transaction Type',
            'mc_source_location.required' => 'Please select Source Location',
            'mc_destination_location.required' => 'Please select Destination Location',
            'mc_itemgeneric.required' => 'Please select Item Generic',
            'mc_qty.required' => 'Please Enter Demand Quantity',
        ];
    }
}
