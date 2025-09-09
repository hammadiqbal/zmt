<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequisitionForMaterialTransferRequest extends FormRequest
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
        // return [
        //     'rmt_org' => 'required',
        //     'rmt_source_site' => 'required',
        //     'rmt_source_location' => 'required',
        //     'rmt_destination_site' => 'required',
        //     'rmt_destination_location' => 'required',
        //     'rmt_transactiontype' => 'required',
        //     'rmt_itemgeneric' => 'required',
        //     'rmt_qty' => 'required',
        // ];

        $rules = [
            'rmt_org' => 'required',
            'rmt_source_site' => 'nullable',
            'rmt_source_location' => 'nullable',
            'rmt_destination_site' => 'nullable',
            'rmt_destination_location' => 'nullable',
            'rmt_transactiontype' => 'required',
            'rmt_itemgeneric' => 'required',
            'rmt_qty' => 'required',
        ];
        if ($this->input('source_applicable') == '1') {
            $rules['rmt_source_site'] = 'required';
            $rules['rmt_source_location'] = 'required';
        }
        if ($this->input('destination_applicable') == '1') {
            $rules['rmt_destination_site'] = 'required';
            $rules['rmt_destination_location'] = 'required';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'rmt_org.required' => 'Please select Organization',
            'rmt_source_site.required' => 'Please select source site',
            'rmt_source_location.required' => 'Please select source location',
            'rmt_destination_site.required' => 'Please select destination site',
            'rmt_destination_location.required' => 'Please select destination location',
            'rmt_transactiontype.required' => 'Please select Transaction Type',
            'rmt_itemgeneric.required' => 'Please select Item Generic',
            'rmt_qty.required' => 'Please Enter Demand Quantity',
        ];
    }
}
