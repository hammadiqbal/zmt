<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeServiceAllocation extends Model
{
    use HasFactory;
    protected $table = 'emp_service_allocation';
    public $timestamps = false;
    protected $fillable = ['emp_id', 'org_id', 'site_id', 'service_id', 'status', 'user_id', 'logid',
    'effective_timestamp', 'last_updated','timestamp'];
}
