<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class ExternalTransactionRequest extends FormRequest
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
        $transactionTypeId = $this->get('et_transactiontype');
        $requireSource = false;
        $requireDestination = false;

        if (!empty($transactionTypeId)) {
            $tt = DB::table('inventory_transaction_type as itt')
                ->leftJoin('inventory_source_destination_type as src', 'src.id', '=', 'itt.source_location_type')
                ->leftJoin('inventory_source_destination_type as dst', 'dst.id', '=', 'itt.destination_location_type')
                ->where('itt.id', $transactionTypeId)
                ->select('src.name as Source', 'dst.name as Destination')
                ->first();

            if ($tt) {
                $src = strtolower($tt->Source ?? '');
                $dst = strtolower($tt->Destination ?? '');
                $requireSource = ($src && (str_contains($src, 'location') || $src === 'vendor' || $src === 'donor' || $src === 'patient'));
                $requireDestination = ($dst && (str_contains($dst, 'location') || $dst === 'vendor' || $dst === 'donor' || $dst === 'patient'));
            }
        }

        return [
            'et_org' => 'required',
            'et_site' => 'required',
            'et_transactiontype' => 'required',
            'et_source' => $requireSource ? 'required' : 'nullable',
            'et_destination' => $requireDestination ? 'required' : 'nullable',
            'et_performing_cc' => 'required',
            'et_generic.*' => 'required',
            'et_brand.*' => 'required',
            'et_batch.*' => 'required',
            'et_expiry.*' => 'required',
            'et_qty.*' => 'required',
        ];
    }


    public function messages()
    {
        return [
            'et_org.required' => 'Please Select Organization',
            'et_site.required' => 'Please Select Site',
            'et_transactiontype.required' => 'Please select Transaction Type',
            'et_source.required' => 'Please select Source',
            'et_destination.required' => 'Please select Destination',
            'et_generic.required' => 'Please select Item Generic',
            'et_brand.required' => 'Please select Item Brand',
            'et_batch.required' => 'Please Enter Batch #',
            'et_expiry.required' => 'Please choose Item Expiry Date',
            'et_qty.required' => 'Please Enter Transaction Qty',
        ];
    }
}
