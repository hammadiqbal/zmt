<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VitalSign extends Model
{
    use HasFactory;
    protected $table = 'vital_sign';
    public $timestamps = false;
    protected $fillable = ['mr_code', 'service_id', 'service_mode_id', 'billing_cc',
    'patient_age', 'sbp', 'dbp',
    'pulse', 'temp', 'r_rate', 'weight', 'height', 'score','o2_saturation', 'bmi','bsa','nursing_notes',
    'status', 'user_id', 'logid',
    'effective_timestamp', 'last_updated','timestamp'];
}
