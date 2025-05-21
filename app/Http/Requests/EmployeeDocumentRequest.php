<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeDocumentRequest extends FormRequest
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
            'document_desc' => 'required',
            'ed_org' => 'required',
            'ed-site' => 'required',
            'empid-document' => 'required',
            'emp_documents' => 'required|array',
            'emp_documents.*' => 'mimes:jpg,jpeg,png,gif,bmp,svg,webp,tiff,ico,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,rtf,odt,ods,odp,epub,csv',
            'ed_edt' => 'required',
        ];
    }


    public function messages()
    {
        return [
            'document_desc.required' => 'Please write description for the documents',
            'ed_org.required' => 'Please Select Organization',
            'ed-site.required' => 'Please Select Site',
            'empid-document.required' => 'Please Select an employee',
            'emp_documents.required' => 'Please add at least one document for an employee.',
            'emp_documents.*.mimes' => 'Employee documents must be of the following types: jpg, jpeg, png, gif, bmp, svg, webp, tiff, ico, pdf, doc, docx, xls, xlsx, ppt, pptx, txt, rtf, odt, ods, odp, epub, csv.',
            'ed_edt.required' => 'Please select Effective Date&Time',
        ];
    }
}
