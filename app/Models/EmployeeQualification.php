<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeQualification extends Model
{
    use HasFactory;
    protected $table = 'emp_qualification';
    public $timestamps = false;
    protected $fillable = ['name','emp_id','level_id','qualification_date',
    'name', 'user_id','effective_timestamp', 'last_updated','timestamp'];
}
