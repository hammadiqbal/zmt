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
        return [
            'vs_mr' => 'required',
            'vs_age' => 'required',
            'vs_sbp' => 'required',
            'vs_dbp' => 'required',
            'vs_pulse' => 'required',
            'vs_temp' => 'required',
            'vs_rrate' => 'required',
            'vs_weight' => 'required',
            'vs_height' => 'required',
            'vs_score' => 'required|integer|between:1,10',
            'vs_o2saturation' => 'required|integer|between:0,100',
            'vs_edt' => 'required',
        ];
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
