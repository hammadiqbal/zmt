<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventorySubCategoryRequest extends FormRequest
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
            'isc_description' => 'required|string',
            'isc_catid' => 'required',
            'isc_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'isc_description.required' => 'Please enter a Inventory Category Description',
            'isc_description.string' => 'Inventory Category Description should contain only alphabetic characters',
            'isc_catid.required' => 'Please select Inventory Category',
            'isc_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
