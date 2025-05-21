<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLocationAllocation extends Model
{
    use HasFactory;
    protected $table = 'emp_inventory_location';
    public $timestamps = false;
    protected $fillable = ['org_id', 'site_id', 'emp_id', 
    'location_site','service_location_id', 'status', 'user_id', 
    'effective_timestamp', 'last_updated','timestamp'];
}
