<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequisitionForMaterialTransfer extends Model
{
    use HasFactory;

    protected $table = 'requisition_material_transfer';
    public $timestamps = false;
    protected $fillable = ['code','org_id', 
    'source_site', 'source_location', 'destination_site', 'destination_location', 
    'transaction_type_id', 'generic_id', 'qty', 'remarks', 'status', 'user_id','logid',
    'effective_timestamp', 'timestamp', 'last_updated'];
}
