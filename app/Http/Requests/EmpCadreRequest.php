<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmpCadreRequest extends FormRequest
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
            'empCadre' => 'required',
            'cadre_org' => 'required',
            'es_edt' => 'required',
        ];
    }


    public function messages()
    {
        return [
            'empCadre.required' => 'Please enter a Employee Cadre',
            'cadre_org.required' => 'Please select Organization',
            'cadre_site.required' => 'Please select Site',
            'es_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
