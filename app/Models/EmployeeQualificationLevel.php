<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeQualificationLevel extends Model
{
    use HasFactory;
    protected $table = 'emp_qualification_level';
    public $timestamps = false;
    protected $fillable = ['name', 'status', 'user_id', 'logid',
    'effective_timestamp', 'last_updated','timestamp'];
}
