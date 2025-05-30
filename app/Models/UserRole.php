<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory;
    protected $table = 'role';
    public $timestamps = false;

    protected $fillable = ['role', 'remarks', 'status',
    'effective_timestamp', 'user_id','logid', 'last_updated', 'timestamp'];
}
