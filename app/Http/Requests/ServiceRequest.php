<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
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
            'services' => 'required',
            's_group' => 'required',
            's_charge' => 'required',
            's_unit' => 'required',
            's_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'services.required' => 'Please enter a Service',
            's_group.required' => 'Please select Service Group',
            's_charge.required' => 'This field is required',
            's_unit.required' => 'This field is required',
            'sg_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
