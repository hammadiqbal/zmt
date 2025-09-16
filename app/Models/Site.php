<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;
    protected $table = 'org_site';
    public $timestamps = false;
    protected $fillable = ['code', 'name', 'remarks', 'org_id', 'old_sitecode', 'address',
    'province_id', 'division_id', 'district_id', 'focalperson_name',
    'email', 'website', 'gps', 'cell_no','landline_no', 'status',
    'user_id', 'logid', 'effective_timestamp', 'last_updated','timestamp'];
}
