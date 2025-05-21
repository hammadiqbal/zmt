<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceBooking extends Model
{
    use HasFactory;
    protected $table = 'service_booking';
    public $timestamps = false;
    protected $fillable = [
        'org_id',
        'site_id',
        'service_location_id',
        'schedule_id',
        'service_id',
        'service_mode_id',
        'billing_cc',
        'emp_id',
        'mr_code',
        'patient_status',
        'patient_priority',
        'remarks',
        'status',
        'user_id',
        'logid',
        'effective_timestamp',
        'timestamp',
        'last_updated',
    ];
}
