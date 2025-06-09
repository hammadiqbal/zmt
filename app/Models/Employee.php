<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    protected $table = 'employee';
    public $timestamps = false;
    protected $fillable = ['name', 'prefix_id', 'guardian_name', 'guardian_relation', 'next_of_kin',
    'next_of_kin_relation','old_code', 'gender_id', 'language', 'religion',
    'marital_status', 'dob', 'org_id', 'site_id', 'cc_id', 'cadre_id',
    'position_id', 'week_hrs', 'report_to', 'q_level_id',
    'emp_status_id', 'work_status_id', 'leaving_date', 'joinig_date', 'address', 'mailing_address',
    'province_id', 'division_id', 'district_id', 'cnic','cnic_expiry',
    'mobile_no', 'additional_mobile_no', 'landline', 'email', 'image',
    'status','user_id', 'logid', 'effective_timestamp', 'last_updated','timestamp'];
}
