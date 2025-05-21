<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventorySourceDestinationTypeRequest extends FormRequest
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
            'invsdt_org' => 'required',
            'invsd_type' => 'required',
            'invsdt_tps' => 'required',
            'invsdt_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'invsdt_org.required' => 'Please Select an Organization',
            'invsd_type.string' => 'Please enter Inventory Type Status',
            'invsdt_tps.required' => 'Please select Third Party Status Category',
            'invsdt_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
