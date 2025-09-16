<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryGenericRequest extends FormRequest
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
            'ig_description' => 'required|string',
            'ig_cat' => 'required',
            'ig_subcat' => 'required',
            'ig_type' => 'required',
            'ig_org' => 'required',
            // 'ig_patientmandatory' => 'required',
            'ig_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'ig_description.required' => 'Please enter a Inventory Generic Description',
            'ig_description.string' => 'Inventory Generic Description should contain only alphabetic characters',
            'ig_cat.required' => 'Please select Inventory Category',
            'ig_subcat.required' => 'Please select Inventory Sub Category',
            'ig_type.required' => 'Please select Inventory Type',
            'ig_org.required' => 'Please select Organization',
            // 'ig_patientmandatory.required' => 'Please select Patient Mandatory Status',
            'ig_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
