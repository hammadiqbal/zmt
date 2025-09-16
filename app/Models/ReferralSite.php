<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralSite extends Model
{
    use HasFactory;
    protected $table = 'referral_site';
    public $timestamps = false;
    protected $fillable = ['name', 'org_id', 'province_id',
    'division_id', 'district_id','cell','landline', 'remarks','status',
    'user_id','logid', 'effective_timestamp', 'last_updated','timestamp'];
}
