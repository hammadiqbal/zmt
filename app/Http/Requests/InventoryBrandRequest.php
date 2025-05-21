<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryBrandRequest extends FormRequest
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
            'ib_description' => 'required|string',
            'ib_cat' => 'required',
            'ib_subcat' => 'required',
            'ib_type' => 'required',
            'ib_generic' => 'required',
            'ib_org' => 'required',
            'ib_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'ib_description.required' => 'Please enter a Inventory Brand Description',
            'ib_description.string' => 'Inventory Brand Description should contain only alphabetic characters',
            'ib_cat.required' => 'Please select Inventory Category',
            'ib_subcat.required' => 'Please select Inventory Sub Category',
            'ib_type.required' => 'Please select Inventory Type',
            'ib_generic.required' => 'Please select Inventory Generic',
            'ib_org.required' => 'Please select Organization',
            'ib_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
