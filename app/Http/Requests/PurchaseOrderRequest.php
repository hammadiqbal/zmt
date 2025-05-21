<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseOrderRequest extends FormRequest
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
            'po_org' => 'required',
            'po_site' => 'required',
            'po_vendor' => 'required',
            'po_brand' => 'required',
            'po_qty' => 'required',
            'po_amount' => 'required',
            'po_discount' => 'required',
            'po_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'po_org.required' => 'Please Select Organization',
            'po_site.required' => 'Please Select Site',
            'po_vendor.required' => 'Please Select Vendor',
            'po_brand.required' => 'Please Select Item Brand',
            'po_qty.required' => 'Please Enter Demand Quantity',
            'po_amount.required' => 'Please Enter Amount',
            'po_discount.required' => 'Please Enter Discount Amount',
            'po_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
