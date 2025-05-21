<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryBrand extends Model
{
    use HasFactory;
    protected $table = 'inventory_brand';
    public $timestamps = false;
    protected $fillable = [
        'name',
        'cat_id',
        'sub_catid',
        'type_id',
        'generic_id',
        'org_id',
        'status',
        'user_id',
        'logid',
        'effective_timestamp',
        'timestamp',
        'last_updated',
    ];
}
