<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryCategoryRequest extends FormRequest
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
            'inv_cat' => 'required|string',
            'ic_org' => 'required',
            'ic_cg' => 'required',
            'ic_cm' => 'required',
            'invcat_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'inv_cat.required' => 'Please enter a Invetory Category Description',
            'inv_cat.string' => 'Invetory Category Description should contain only alphabetic characters',
            'ic_org.required' => 'Please Select Organization',
            'ic_cg.required' => 'Please Select Consumption Group',
            'ic_cm.required' => 'Please Select Consumption Method',
            'invcat_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
