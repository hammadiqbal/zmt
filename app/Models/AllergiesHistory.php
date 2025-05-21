<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllergiesHistory extends Model
{
    use HasFactory;
    protected $table = 'allergies_history';
    public $timestamps = false;
    protected $fillable = ['mr_code', 'service_id', 'service_mode_id',
    'billing_cc', 'patient_age','history','since_date', 'status',
    'user_id','logid', 'effective_timestamp', 'last_updated','timestamp'];
}
