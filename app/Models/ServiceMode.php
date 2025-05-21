<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceMode extends Model
{
    use HasFactory;
    protected $table = 'service_mode';
    public $timestamps = false;
    protected $fillable = ['code', 'billing_mode', 'name', 'status', 'user_id', 'logid',
    'effective_timestamp', 'last_updated','timestamp'];
}
