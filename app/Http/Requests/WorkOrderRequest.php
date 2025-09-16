<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkOrderRequest extends FormRequest
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
            'wo_org' => 'required',
            'wo_site' => 'required',
            'wo_vendor' => 'required',
            'wo_particulars' => 'required',
            'wo_amount' => 'required',
            'wo_discount' => 'required',
            'wo_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'wo_org.required' => 'Please select Organization',
            'wo_site.required' => 'Please select Site',
            'wo_vendor.required' => 'Please select Vendor',
            'wo_particulars.required' => 'Please Enter Particulars.',
            'wo_amount.required' => 'Please Enter Amount',
            'wo_discount.required' => 'Please Enter Discount',
            'wo_edt.required' => 'The effective date and time field is required.',
        ];
    }
}
