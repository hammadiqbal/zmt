<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SLActivationRequest extends FormRequest
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
            'sl_org' => 'required',
            'sl_site' => 'required',
            'sl_name' => 'required',
            'a_sl_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'sl_org.required' => 'Please Select Organization',
            'sl_site.required' => 'Please Select Site',
            'sl_name.required' => 'Please Select Cost Center',
            'a_sl_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
