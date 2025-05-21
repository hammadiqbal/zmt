<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadReportRequest extends FormRequest
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
            'it_report' => 'required', 
            'it_report.*' => 'mimes:pdf,doc,docx,txt,rtf,odt,ods,xls,xlsx,csv', 
        ];
    }

    public function messages()
    {
        return [
            'it_report.required' => 'Please upload at least one attachment.',
            'it_report.*.mimes' => 'One or more uploaded files have an invalid file type. Only PDF, DOC, DOCX, RTF, ODT, ODS, XLS, XLSX, CSV, or TXT files are accepted.',
        ];
    }
}
