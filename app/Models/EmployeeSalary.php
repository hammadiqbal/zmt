<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalary extends Model
{
    use HasFactory;
    protected $table = 'emp_salary';
    public $timestamps = false;
    protected $fillable = ['name', 'emp_id', 'additions', 'deductions', 'remarks', 'status', 'user_id', 'logid',
    'effective_timestamp', 'last_updated','timestamp'];
}
