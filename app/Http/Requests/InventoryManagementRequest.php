<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryManagementRequest extends FormRequest
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
            'im_transactiontype' => 'required',
            'im_org' => 'required',
            'im_site' => 'required',
            'im_batch_no' => 'required',
            'im_expiry' => 'required',
            'im_rate' => 'required',
            'im_qty' => 'required',
            'im_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'im_transactiontype.required' => 'Please select Inventory Transaction Type',
            'im_org.required' => 'Please select Organization',
            'im_site.required' => 'Please select Site',
            'im_batch_no.required' => 'Please enter Item Batch No.',
            'im_expiry.required' => 'Please select Expiry Date',
            'im_rate.required' => 'Please select Item Rate',
            'im_qty.required' => 'Please select Transaction Quantity',
            'im_edt.required' => 'Please select Effective Date&Time',
        ];
    }
    
}
