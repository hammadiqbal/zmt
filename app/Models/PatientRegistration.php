<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientRegistration extends Model
{
    use HasFactory;
    protected $table = 'patient';
    public $timestamps = false;
    protected $fillable = ['mr_code', 'name', 'guardian', 'next_of_kin',
    'relation', 'language', 'religion', 'marital_status', 'old_mrcode',
    'gender_id', 'dob', 'org_id', 'site_id', 'house_no', 'address',
    'province_id', 'division_id', 'district_id', 'cnic', 'family_no',
    'cell_no', 'additional_cellno', 'landline', 'email', 'img',
    'status','user_id', 'logid', 'effective_timestamp', 'last_updated','timestamp'];
}
