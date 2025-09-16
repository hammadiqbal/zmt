<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeCC extends Model
{
    use HasFactory;
    protected $table = 'emp_cc';
    public $timestamps = false;
    protected $fillable = ['emp_id','org_id','site_id','headcount_site_id',
    'cc_id','cc_id','percentage', 'user_id','logid','last_updated','timestamp'];
}
