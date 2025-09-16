<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeStatus extends Model
{
    use HasFactory;
    protected $table = 'emp_status';
    public $timestamps = false;
    protected $fillable = ['name', 'status', 'user_id', 'logid',
    'effective_timestamp', 'last_updated','timestamp'];
}
