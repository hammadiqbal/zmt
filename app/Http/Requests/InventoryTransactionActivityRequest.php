<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryTransactionActivityRequest extends FormRequest
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
            'invta_org' => 'required',
            'inv_ta' => 'required',
            'invta_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'invta_org.required' => 'Please Select an Organization',
            'inv_ta.string' => 'Please enter Transaction Activity Description',
            'invta_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
