<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceGroupRequest extends FormRequest
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
            'sg_name' => 'required',
            'sg_code' => 'required',
            'sg_type' => 'required',
            'sg_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'sg_name.required' => 'Please enter a Service Group',
            'sg_code.required' => 'Please enter Service Code',
            'sg_type.required' => 'Please select Service Type',
            'sg_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
