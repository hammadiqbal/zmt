<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceLocationRequest extends FormRequest
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
            'service_location' => 'required|string',
            'sl_org' => 'required',
            // 'sl_site' => 'required',
            'inv_status' => 'required',
            'sl_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'service_location.required' => 'Please enter a Service Location Description',
            'service_location.string' => 'Service Location Description should contain only alphabetic characters',
            'sl_org.required' => 'Please select Organization',
            // 'sl_site.required' => 'Please select Site',
            'inv_status.required' => 'Please select Inventory Status',
            'sl_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
