<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KPIGroups extends Model
{
    use HasFactory;
    protected $table = 'kpi_group';
    public $timestamps = false;
    protected $fillable = [
        'code',
        'name',
        'status',
        'user_id',
        'logid',
        'effective_timestamp',
        'timestamp',
        'last_updated',
    ];
}
