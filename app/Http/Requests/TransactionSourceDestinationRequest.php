<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionSourceDestinationRequest extends FormRequest
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
            'transactionsd' => 'required|string',
            'tsd_org' => 'required',
            'tsd_edt' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'transactionsd.required' => 'Please enter Transaction Source/Destination',
            'tsd_org.required' => 'Please select Organization',
            'tsd_edt.required' => 'The effective date and time field is required.',
        ];
    }
}
