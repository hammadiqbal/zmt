<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeGender extends Model
{
    use HasFactory;
    protected $table = 'gender';
    public $timestamps = false;
    protected $fillable = ['name', 'status', 'user_id', 'logid',
    'effective_timestamp', 'last_updated','timestamp'];
}
