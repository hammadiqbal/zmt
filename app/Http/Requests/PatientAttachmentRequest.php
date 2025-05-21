<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PatientAttachmentRequest extends FormRequest
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
            'pattachement_desc' => 'required',
            'pattachement_date' => 'required',
            'patient_attachments' => 'required', // Ensure at least one file is uploaded
            // 'patient_attachments.*' => 'mimes:pdf,doc,docx,txt,rtf,odt,ods,xls,xlsx,csv', // Validate each file type
        ];
    }

    public function messages()
    {
        return [
            'pattachement_desc.required' => 'The attachment description is required.',
            'pattachement_date.required' => 'The attachment date is required.',
            'patient_attachments.required' => 'Please upload at least one attachment.',
            // 'patient_attachments.*.mimes' => 'One or more uploaded files have an invalid file type. Only PDF, DOC, DOCX, RTF, ODT, ODS, XLS, XLSX, CSV, or TXT files are accepted.',
        ];
    }
}
