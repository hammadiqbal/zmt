<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitBasedDetails extends Model
{
    use HasFactory;
    protected $table = 'visit_based_details';
    public $timestamps = false;
    protected $fillable = ['mr_code', 'emp_id', 'service_id', 'service_mode_id',
    'billing_cc', 'patient_age','complaints','clinical_notes', 'summary', 
    'user_id','logid', 'last_updated','timestamp'];
}
