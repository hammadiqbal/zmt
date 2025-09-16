<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRateRequest extends FormRequest
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
            'rate_unitCost' => 'required',
            'rate_billedAmount' => 'required',
        ];
    }


    public function messages()
    {
        return [
            'rate_unitCost.required' => 'Please enter Unit Cost Amount',
            'rate_billedAmount.required' => 'Please enter billed amount',
        ];
    }
}
