<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    use HasFactory;
    protected $table = 'service_type';
    public $timestamps = false;
    protected $fillable = ['code', 'name', 'status', 'user_id', 'logid',
    'effective_timestamp', 'last_updated','timestamp'];
}
