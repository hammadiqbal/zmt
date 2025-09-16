<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryBalance extends Model
{
    use HasFactory;
    protected $table = 'inventory_balance';
    public $timestamps = false;
    protected $fillable = [
        'org_id',
        'site_id',
        'management_id',
        'generic_id',
        'brand_id',
        'batch_no',
        'org_balance',
        'site_balance',
        'location_id',
        'location_balance',
        'remarks',
        'timestamp',
    ];
}
