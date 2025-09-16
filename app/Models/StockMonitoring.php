<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMonitoring extends Model
{
    use HasFactory;
    protected $table = 'stock_monitoring';
    public $timestamps = false;
    protected $fillable = ['org_id', 'site_id', 'item_generic_id', 'item_brand_id', 'service_location_id',  
    'min_stock', 'max_stock', 'monthly_consumption_ceiling', 'min_reorder_qty', 'primary_email','secondary_email','status', 'user_id', 'logid',
    'effective_timestamp', 'last_updated','timestamp'];
}
