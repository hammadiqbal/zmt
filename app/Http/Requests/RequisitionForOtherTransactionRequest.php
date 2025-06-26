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
        return [
            'rot_org' => 'required',
            'rot_site' => 'required',
            'rot_transactiontype' => 'required',
            'rot_inv_location' => 'required',
            'rot_itemgeneric' => 'required',
            'rot_qty' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'rot_org.required' => 'Please select Organization',
            'rot_site.required' => 'Please select Site',
            'rot_transactiontype.required' => 'Please select Transaction Type',
            'rot_inv_location.required' => 'Please select Inventory Location',
            'rot_itemgeneric.required' => 'Please select Item Generic',
            'rot_qty.required' => 'Please Enter Demand Quantity',
        ];
    }
}
