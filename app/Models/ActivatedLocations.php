<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivatedLocations extends Model
{
    use HasFactory;
    protected $table = 'activated_location';
    public $timestamps = false;
    protected $fillable = ['org_id','site_id', 'location_id', 'status',
    'user_id','logid', 'effective_timestamp', 'last_updated','timestamp'];
}
