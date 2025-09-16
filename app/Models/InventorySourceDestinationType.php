<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventorySourceDestinationType extends Model
{
    use HasFactory;
    protected $table = 'inventory_source_destination_type';
    public $timestamps = false;
    protected $fillable = [
        'org_id',
        'name',
        'third_party',
        'status',
        'user_id',
        'logid',
        'effective_timestamp',
        'timestamp',
        'last_updated',
    ];
}
