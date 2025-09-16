<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequisitionForEPI extends Model
{
    use HasFactory;
    protected $table = 'req_epi';
    public $timestamps = false;
    protected $fillable = ['mr_code', 'org_id', 'site_id', 'patient_age', 'service_id', 'service_mode_id',
    'billing_cc', 'emp_id','action', 'remarks','status', 'user_id',
    'logid','effective_timestamp', 'timestamp','last_updated'];
}
