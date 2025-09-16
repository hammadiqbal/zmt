<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeCadre extends Model
{
    use HasFactory;
    protected $table = 'emp_cadre';
    public $timestamps = false;
    protected $fillable = ['name', 'org_id', 'status', 'user_id', 'logid',
    'effective_timestamp', 'last_updated','timestamp'];
}
