<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    use HasFactory;
    protected $table = 'work_order';
    public $timestamps = false;
    protected $fillable = [
        'org_id',
        'site_id',
        'vendor_id',
        'particulars',
        'amount',
        'discount',
        'remarks',
        'approval',
        'approved_by',
        'approved_timestamp',
        'status',
        'user_id',
        'logid',
        'effective_timestamp',
        'timestamp',
        'last_updated',
    ];
}
