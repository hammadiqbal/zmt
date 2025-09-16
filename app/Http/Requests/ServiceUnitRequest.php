<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceUnitRequest extends FormRequest
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
            'su_name' => 'required',
            'su_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'su_name.required' => 'Please enter a Service Unit',
            'su_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
