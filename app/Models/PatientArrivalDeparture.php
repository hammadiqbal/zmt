<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientArrivalDeparture extends Model
{
    use HasFactory;
    protected $table = 'patient_inout';
    public $timestamps = false;
    protected $fillable = [
        'org_id',
        'site_id',
        'service_location_id',
        'schedule_id',
        'mr_code',
        'service_id',
        'service_mode_id',
        'billing_cc',
        'emp_id',
        'patient_status',
        'patient_priority',
        'remarks',
        // 'amount',
        // 'payment_mode',
        'service_start_time',
        'service_end_time',
        'status',
        'user_id',
        'logid',
        'timestamp',
        'last_updated',
    ];
}
