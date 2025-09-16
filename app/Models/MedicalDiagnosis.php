<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalDiagnosis extends Model
{
    use HasFactory;
    protected $table = 'medical_diagnosis';
    public $timestamps = false;
    protected $fillable = ['mr_code', 'service_id', 'service_mode_id',
    'billing_cc', 'patient_age','icd_id','since_date','till_date','status',
    'user_id','logid', 'effective_timestamp', 'last_updated','timestamp'];
}
