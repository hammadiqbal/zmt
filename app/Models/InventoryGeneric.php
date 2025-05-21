<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryGeneric extends Model
{
    use HasFactory;
    protected $table = 'inventory_generic';
    public $timestamps = false;
    protected $fillable = [
        'name',
        'cat_id',
        'sub_catid',
        'type_id',
        'org_id',
        'patient_mandatory',
        'status',
        'user_id',
        'logid',
        'effective_timestamp',
        'timestamp',
        'last_updated',
    ];
}
