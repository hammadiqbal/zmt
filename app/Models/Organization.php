<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;
    protected $table = 'organization';
    public $timestamps = false;
    protected $fillable = ['code', 'organization', 'remarks', 'headoffice_address',
    'province_id', 'division_id', 'district_id', 'focalperson_name',
    'email', 'website', 'gps', 'cell_no',
    'landline_no', 'logo', 'banner', 'status',
    'user_id', 'logid', 'effective_timestamp', 'last_updated','timestamp'];
}
