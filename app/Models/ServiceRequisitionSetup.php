<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequisitionSetup extends Model
{
    use HasFactory;
    protected $table = 'service_requisition_setup';
    public $timestamps = false;
    protected $fillable = ['service_id', 'mandatory', 'description', 'org_id', 'status', 'user_id', 'logid',
    'effective_timestamp', 'last_updated','timestamp'];
}
