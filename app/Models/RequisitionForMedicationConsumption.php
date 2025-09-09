<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequisitionForMedicationConsumption extends Model
{
    use HasFactory;
    protected $table = 'req_medication_consumption';
    public $timestamps = false;
    protected $fillable = ['code','transaction_type_id', 
    'source_location_id', 'destination_location_id', 'mr_code', 'gender_id',
    'age', 'service_id','org_id', 'site_id','service_mode_id', 'service_type_id',
    'service_group_id', 'responsible_physician','billing_cc', 'inv_generic_ids','dose','route_ids','frequency_ids',
    'days','remarks','user_id','status',
    'logid','effective_timestamp', 'timestamp','last_updated'];
}
