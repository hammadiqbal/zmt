<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryCategory extends Model
{
    use HasFactory;
    protected $table = 'inventory_category';
    public $timestamps = false;
    protected $fillable = [
        'name',
        'org_id',
        'consumption_group',
        'consumption_method',
        'status',
        'user_id',
        'logid',
        'effective_timestamp',
        'timestamp',
        'last_updated',
    ];
}
