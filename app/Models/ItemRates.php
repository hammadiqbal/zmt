<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemRates extends Model
{
    use HasFactory;
    protected $table = 'item_rates';
    public $timestamps = false;
    protected $fillable = ['org_id', 'site_id', 'brand_id','batch_no', 'unit_cost', 'billed_amount',
    'status', 'user_id', 'logid',
    'effective_timestamp', 'last_updated','timestamp'];
}
