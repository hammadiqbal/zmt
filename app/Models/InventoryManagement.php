<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryManagement extends Model
{
    use HasFactory;
    protected $table = 'inventory_management';
    public $timestamps = false;
    protected $fillable = [
        'transaction_type_id',
        'org_id',
        'site_id',
        'ref_document_no',
        'third_paty_id',
        'mr_code',
        'service_id',
        'service_mode_id',
        'billing_cc',
        'performing_cc',
        'resp_physician',
        'source',
        'destination',
        'source_balance',
        'destination_balance',
        'site_balance',
        'org_balance',
        'remarks',
        'brand_id',
        'batch_no',
        'expiry_date',
        'transaction_qty',
        'inv_generic_id',
        'status',
        'user_id',
        'logid',
        'effective_timestamp',
        'timestamp',
        'last_updated',
    ];
}
