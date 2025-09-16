<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivatedServiceRate extends Model
{
    use HasFactory;
    protected $table = 'activated_service_rate';
    public $timestamps = false;
    protected $fillable = ['activated_service_id','service_mode_id', 'cost_price', 'sell_price',
    'user_id','logid', 'last_updated','timestamp'];
}
