<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransactionType extends Model
{
    use HasFactory;
    protected $table = 'inventory_transaction_type';
    public $timestamps = false;
    protected $fillable = [
        'name',
        'activity_type',
        'request_mandatory',
        'emp_location_mandatory_request',
        'source_location_type',
        'source_action',
        'destination_location_type',
        'destination_action',
        'source_location',
        'destination_location',
        'emp_location_source_destination',
        'transaction_expired_status',
        'org_id',
        'status',
        'user_id',
        'logid',
        'effective_timestamp',
        'timestamp',
        'last_updated',
    ];
}
