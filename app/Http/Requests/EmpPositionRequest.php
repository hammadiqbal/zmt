<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmpPositionRequest extends FormRequest
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
            'empPosition' => 'required',
            'positionOrg' => 'required',
            'emp-cadre' => 'required',
            'ep_edt' => 'required',
        ];
    }


    public function messages()
    {
        return [
            'empPosition.required' => 'Please enter a Employee Cadre',
            'positionOrg.required' => 'Please select Organization',
            'ep_edt.required' => 'Please select Effective Date&Time',
            'emp-cadre.required' => 'Please select Employee Cadre',
        ];
    }
}
