<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SampleTrackingRequest extends FormRequest
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
            // 'it_remarks' => 'required',
            // 'it_report' => 'required', 
            // 'it_report.*' => 'mimes:pdf,doc,docx,txt,rtf,odt,ods,xls,xlsx,csv', 
            'it_confirmation' => 'required',

        ];
    }

    public function messages()
    {
        return [
            // 'it_remarks.required' => 'This field is required.',
            'it_confirmation.required' => 'Investigation confirmation date&time is required.',
            // 'it_report.required' => 'Please upload at least one attachment.',
            // 'it_report.*.mimes' => 'One or more uploaded files have an invalid file type. Only PDF, DOC, DOCX, RTF, ODT, ODS, XLS, XLSX, CSV, or TXT files are accepted.',
        ];
    }
}
