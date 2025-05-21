<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmpStatusRequest extends FormRequest
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
            'empStatus_name' => 'required',
            'es_edt' => 'required',
        ];
    }


    public function messages()
    {
        return [
            'empStatus_name.required' => 'Please enter a Employee Status',
            'es_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
