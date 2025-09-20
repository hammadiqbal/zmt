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
        $ageValue = $this->input('vs_age');
        $patientAge = 0;
        
        // Check if ageValue contains "years" (indicating it's already calculated age)
        if ($ageValue && strpos($ageValue, 'years') !== false) {
            // Extract the number before "years"
            preg_match('/(\d+)\s*years/', $ageValue, $matches);
            if (isset($matches[1])) {
                $patientAge = (int) $matches[1];
            }
        } else if ($ageValue && (strpos($ageValue, '/') !== false || strpos($ageValue, '-') !== false)) {
            // If it's a date, calculate age from date of birth
            try {
                $birthDate = new \DateTime($ageValue);
                $today = new \DateTime();
                $patientAge = $today->diff($birthDate)->y;
            } catch (\Exception $e) {
                // If date parsing fails, try to parse as number
                $patientAge = (float) $ageValue;
            }
        } else {
            // If it's already a number, use it directly
            $patientAge = (float) $ageValue;
        }
        
        $isUnder16 = $patientAge < 16;
        
        // Debug logging (remove in production)
        \Log::info('VitalSign Validation - Age Value: ' . $ageValue);
        \Log::info('VitalSign Validation - Calculated Age: ' . $patientAge);
        \Log::info('VitalSign Validation - Is Under 16: ' . ($isUnder16 ? 'true' : 'false'));
        
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
        
        // Make SBP, DBP, and Pain Score optional for patients under 16
        if ($isUnder16) {
            $rules['vs_sbp'] = 'nullable|integer|min:0|max:300';
            $rules['vs_dbp'] = 'nullable|integer|min:0|max:200';
            $rules['vs_score'] = 'nullable|integer|between:1,10';
        } else {
            $rules['vs_sbp'] = 'required|integer|min:0|max:300';
            $rules['vs_dbp'] = 'required|integer|min:0|max:200';
            $rules['vs_score'] = 'required|integer|between:1,10';
        }
        
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
