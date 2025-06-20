<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutsourcedServices extends Model
{
    use HasFactory;
    protected $table = 'outsourced_services';
    public $timestamps = false;
    protected $fillable = [
        'org_id',
        'site_id',
        'mr_code',
        'referral_site',
        'billing_cc',
        'physician',
        'service_mode',
        'service_id',
        'service_desc',
        'remarks',
        'start_time',
        'end_time',
        'billed_amount',
        'status',
        'user_id',
        'logid',
        'effective_timestamp',
        'timestamp',
        'last_updated'
    ];
} 