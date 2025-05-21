<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceLocationScheduling extends Model
{
    use HasFactory;
    protected $table = 'service_location_scheduling';
    public $timestamps = false;
    protected $fillable = ['name', 'org_id', 'site_id', 'service_location_id',
    'start_timestamp', 'end_timestamp', 'schedule_pattern', 'total_patient_limit',
    'new_patient_limit', 'followup_patient_limit', 'routine_patient_limit',
    'urgent_patient_limit','emp_id','status', 'user_id', 'logid',
    'effective_timestamp', 'last_updated','timestamp'];
}
