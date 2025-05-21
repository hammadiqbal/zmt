<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KPIDimensions extends Model
{
    use HasFactory;
    protected $table = 'kpi_dimension';
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
