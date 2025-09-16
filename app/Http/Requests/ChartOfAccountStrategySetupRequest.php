<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChartOfAccountStrategySetupRequest extends FormRequest
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
            'ass_org' => 'required',
            'ass_level' => 'required',
            'ass_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'ass_org.required' => 'Please Select Organization',
            'ass_level.string' => 'Please Select Account Level Code',
            'as_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
