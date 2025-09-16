<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KPIActivation extends Model
{
    use HasFactory;
    protected $table = 'activated_kpi';
    public $timestamps = false;
    protected $fillable = ['kpi_id', 'org_id', 'site_id', 'cc_id',
    'status', 'user_id', 'logid',
    'effective_timestamp', 'last_updated','timestamp'];
}
