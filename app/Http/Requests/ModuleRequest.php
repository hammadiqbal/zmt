<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ModuleRequest extends FormRequest
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
            'parent_module' => 'required',
            'module_name' => 'required|string',
        ];
    }
    public function messages()
    {
        return [
            'module_name.required' => 'Please enter Module Name',
            'module_name.string' => 'Module Name should contain only alphabetic characters',
            'parent_module.required' => 'Please select Parent',
        ];
    }
}
