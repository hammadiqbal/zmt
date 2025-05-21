<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeMedicalLicense extends Model
{
    use HasFactory;
    protected $table = 'emp_medical_license';
    public $timestamps = false;
    protected $fillable = ['name','emp_id','name','ref_no',
    'expire_date','status', 'user_id','last_updated','timestamp'];
}
