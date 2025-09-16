<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemRatesRequest extends FormRequest
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
            'ir_org' => 'required',
            'ir_site' => 'required',
            'ir_generic' => 'required',
            'ir_brand' => 'required',
            'ir_batch' => 'required',
            'ir_packsize' => 'required|integer|min:1',
            'ir_unitcost' => 'required|numeric',
            'ir_billedamount' => 'required|numeric',
            'ir_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'ir_org.required' => 'Please Select Organization',
            'ir_site.required' => 'Please Select Site',
            'ir_generic.required' => 'Please Select Item Generic',
            'ir_brand.required' => 'Please Select Brand',
            'ir_batch.required' => 'Please Select Batch Number',
            'ir_packsize.required' => 'Please Enter Pack Size',
            'ir_packsize.integer' => 'Pack Size must be a whole number',
            'ir_packsize.min' => 'Pack Size must be at least 1',
            'ir_unitcost.required' => 'Please enter Unit Cost',
            'ir_unitcost.numeric' => 'Unit Cost must be a valid number',
            'ir_billedamount.required' => 'Please enter Billed Amount',
            'ir_billedamount.numeric' => 'Billed Amount must be a valid number',
            'ir_edt.required' => 'Please select Effective Date&Time',
        ];
    }

}
