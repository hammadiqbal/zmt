<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventorySubCategory extends Model
{
    use HasFactory;
    protected $table = 'inventory_subcategory';
    public $timestamps = false;
    protected $fillable = [
        'name',
        'cat_id',
        'status',
        'user_id',
        'logid',
        'effective_timestamp',
        'timestamp',
        'last_updated',
    ];
}
