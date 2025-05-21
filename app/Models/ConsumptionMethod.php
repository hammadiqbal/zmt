<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumptionMethod extends Model
{
    use HasFactory;
    protected $table = 'consumption_method';
    public $timestamps = false;
    protected $fillable = [
        'org_id',
        'description',
        'criteria',
        'group_id',
        'status',
        'user_id',
        'logid',
        'effective_timestamp',
        'timestamp',
        'last_updated',
    ];
}
