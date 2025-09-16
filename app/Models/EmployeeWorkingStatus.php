<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeWorkingStatus extends Model
{
    use HasFactory;
    protected $table = 'emp_working_status';
    public $timestamps = false;
    protected $fillable = ['name', 'job_continue', 'status', 'user_id', 'logid',
    'effective_timestamp', 'last_updated','timestamp'];
}
