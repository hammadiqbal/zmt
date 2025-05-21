<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceActivation extends Model
{
    use HasFactory;
    protected $table = 'activated_service';
    public $timestamps = false;
    protected $fillable = ['org_id', 'site_id', 'service_id', 'ordering_cc_ids', 'performing_cc_ids',
    'servicemode_ids','status', 'user_id', 'logid',
    'effective_timestamp', 'last_updated','timestamp'];
}
