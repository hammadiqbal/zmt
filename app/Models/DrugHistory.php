<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrugHistory extends Model
{
    use HasFactory;

    protected $table = 'drug_history';
    public $timestamps = false;
    protected $fillable = ['mr_code', 'service_id', 'service_mode_id',
    'billing_cc', 'patient_age','history','dose', 'status',
    'user_id','logid', 'effective_timestamp', 'last_updated','timestamp'];
}
