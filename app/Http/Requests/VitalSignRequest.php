<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VitalSignRequest extends FormRequest
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
        $patientAge = $this->input('vs_age');
        $isUnder16 = $patientAge && $patientAge < 16;
        
        $rules = [
            'vs_mr' => 'required',
            'vs_age' => 'required',
            'vs_pulse' => 'required',
            'vs_temp' => 'required',
            'vs_rrate' => 'required',
            'vs_weight' => 'required',
            'vs_height' => 'required',
            'vs_o2saturation' => 'required|integer|between:0,100',
            'vs_edt' => 'required',
        ];
        
        // // Make SBP, DBP, and Pain Score optional for patients under 16
        // if ($isUnder16) {
        //     $rules['vs_sbp'] = 'nullable|integer|min:0|max:300';
        //     $rules['vs_dbp'] = 'nullable|integer|min:0|max:200';
        //     $rules['vs_score'] = 'nullable|integer|between:1,10';
        // } else {
        //     $rules['vs_sbp'] = 'required|integer|min:0|max:300';
        //     $rules['vs_dbp'] = 'required|integer|min:0|max:200';
        //     $rules['vs_score'] = 'required|integer|between:1,10';
        // }
        
        return $rules;
    }

    public function messages()
    {
        return [
            'vs_score.required' => 'This field is required',
            'vs_score.between' => 'Score must be b/w 1 & 10',
            'vs_o2saturation.required' => 'This field is required',
            'vs_o2saturation.between' => 'Score must be b/w 0 & 100',
        ];
    }
}
