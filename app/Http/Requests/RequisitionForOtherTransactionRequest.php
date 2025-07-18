<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequisitionForOtherTransactionRequest extends FormRequest
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
        //     'rot_org' => 'required',
        //     'rot_source_site' => 'required',
        //     'rot_source_location' => 'required',
        //     'rot_destination_site' => 'required',
        //     'rot_destination_location' => 'required',
        //     'rot_transactiontype' => 'required',
        //     'rot_itemgeneric' => 'required',
        //     'rot_qty' => 'required',
        // ];

        $rules = [
            'rot_org' => 'required',
            'rot_source_site' => 'nullable',
            'rot_source_location' => 'nullable',
            'rot_destination_site' => 'nullable',
            'rot_destination_location' => 'nullable',
            'rot_transactiontype' => 'required',
            'rot_itemgeneric' => 'required',
            'rot_qty' => 'required',
        ];
        if ($this->input('source_applicable') == '1') {
            $rules['rot_source_site'] = 'required';
            $rules['rot_source_location'] = 'required';
        }
        if ($this->input('destination_applicable') == '1') {
            $rules['rot_destination_site'] = 'required';
            $rules['rot_destination_location'] = 'required';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'rot_org.required' => 'Please select Organization',
            'rot_source_site.required' => 'Please select source site',
            'rot_source_location.required' => 'Please select source location',
            'rot_destination_site.required' => 'Please select destination site',
            'rot_destination_location.required' => 'Please select destination location',
            'rot_transactiontype.required' => 'Please select Transaction Type',
            'rot_itemgeneric.required' => 'Please select Item Generic',
            'rot_qty.required' => 'Please Enter Demand Quantity',
        ];
    }
}
