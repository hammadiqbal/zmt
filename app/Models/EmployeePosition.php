<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeePosition extends Model
{
    use HasFactory;
    protected $table = 'emp_position';
    public $timestamps = false;
    protected $fillable = ['name', 'org_id', 'cadre_id',
    'status', 'user_id', 'logid',
    'effective_timestamp', 'last_updated','timestamp'];
}
