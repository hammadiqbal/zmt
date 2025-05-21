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
        'cat_id',
        'activity_type',
        'request_mandatory',
        'request_location_mandatory',
        'source_location_type',
        'source_action',
        'destination_location_type',
        'destination_action',
        'service_location_id',
        'applicable_location_to',
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
