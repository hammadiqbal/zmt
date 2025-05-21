<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KPITypes extends Model
{
    use HasFactory;
    protected $table = 'kpi_type';
    public $timestamps = false;
    protected $fillable = [
        'code',
        'name',
        'group_id',
        'dimension_id',
        'status',
        'user_id',
        'logid',
        'effective_timestamp',
        'timestamp',
        'last_updated',
    ];
}
