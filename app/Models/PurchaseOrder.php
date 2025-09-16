<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;
    protected $table = 'purchase_order';
    public $timestamps = false;
    protected $fillable = [
        'org_id',
        'site_id',
        'vendor_id',
        'inventory_brand_id',
        'demand_qty',
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
