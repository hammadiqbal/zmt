<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceLocation extends Model
{
    use HasFactory;
    protected $table = 'service_location';
    public $timestamps = false;
    protected $fillable = ['name', 'org_id', 'inventory_status',
    'status', 'user_id', 'logid',
    'effective_timestamp', 'last_updated','timestamp'];
}
