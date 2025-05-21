<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    protected $table = 'services';
    public $timestamps = false;
    protected $fillable = ['name', 'group_id', 'charge', 'unit_id',
    'status', 'user_id', 'logid',
    'effective_timestamp', 'last_updated','timestamp'];
}
