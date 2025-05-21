<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryTypeRequest extends FormRequest
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
            'it_description' => 'required|string',
            'it_cat' => 'required',
            'it_subcat' => 'required',
            'it_org' => 'required',
            'it_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'it_description.required' => 'Please enter a Inventory Category Description',
            'it_description.string' => 'Inventory Category Description should contain only alphabetic characters',
            'it_cat.required' => 'Please select Inventory Category',
            'it_subcat.required' => 'Please select Sub Category',
            'it_org.required' => 'Please select Organization',
            'it_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
