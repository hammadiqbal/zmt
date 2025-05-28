<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialConsumptionRequisition extends Model
{
    use HasFactory;
    protected $table = 'material_consumption_requisition';
    public $timestamps = false;
    protected $fillable = ['code','org_id', 'site_id', 'transaction_type_id','inv_location_id',
    'mr_code', 'patient_age', 'patient_gender_id', 'service_id','service_mode_id',
    'billing_cc', 'physician_id', 'generic_id', 'qty', 'remarks', 'status', 'user_id','logid',
    'effective_timestamp', 'timestamp', 'last_updated'];
}
