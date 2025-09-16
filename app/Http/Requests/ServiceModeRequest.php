<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceModeRequest extends FormRequest
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
            'sm_name' => 'required',
            'sm_code' => 'required',
            'billing_mode' => 'required',
            'sm_edt' => 'required',
        ];
    }


    public function messages()
    {
        return [
            'sm_name.required' => 'Please enter a Service Mode',
            'sm_code.required' => 'Please enter code',
            'billing_mode.required' => 'Please select Billing Mode',
            'sm_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
