<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmpWorkingStatusRequest extends FormRequest
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
            'workingStatus' => 'required',
            'ews_edt' => 'required',
        ];
    }


    public function messages()
    {
        return [
            'workingStatus.required' => 'Please enter a Employee Working Status',
            'ews_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
