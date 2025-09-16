<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CCType extends Model
{
    use HasFactory;
    protected $table = 'cc_type';
    public $timestamps = false;
    protected $fillable = [
        'type',
        'remarks',
        'ordering',
        'performing',
        'status',
        'user_id',
        'logid',
        'effective_timestamp',
        'timestamp',
        'last_updated',
    ];
}
