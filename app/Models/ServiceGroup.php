<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceGroup extends Model
{
    use HasFactory;
    protected $table = 'service_group';
    public $timestamps = false;
    protected $fillable = ['code', 'name', 'status', 'type_id', 'user_id', 'logid',
    'effective_timestamp', 'last_updated','timestamp'];
}
