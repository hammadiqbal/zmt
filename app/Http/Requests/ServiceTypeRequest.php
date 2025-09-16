<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceTypeRequest extends FormRequest
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
            'st_name' => 'required',
            'st_code' => 'required',
            'st_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'st_name.required' => 'Please enter a Service Type',
            'st_code.required' => 'Please enter Service Code',
            'st_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
