<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImmunizationHistory extends Model
{
    use HasFactory;

    protected $table = 'immunization_history';
    public $timestamps = false;
    protected $fillable = ['mr_code', 'service_id', 'service_mode_id',
    'billing_cc', 'patient_age','history','date', 'status',
    'user_id','logid', 'effective_timestamp', 'last_updated','timestamp'];
}
