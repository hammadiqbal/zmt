<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumptionGroup extends Model
{
    use HasFactory;
    protected $table = 'consumption_group';
    public $timestamps = false;
    protected $fillable = [
        'description',
        'remarks',
        'org_id',
        'status',
        'user_id',
        'logid',
        'effective_timestamp',
        'timestamp',
        'last_updated',
    ];
}
