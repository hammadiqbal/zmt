<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{

    use HasFactory;
    protected $table = 'district';
    public $timestamps = false;
    protected $fillable = [
        // Common columns for all tables
        'name',
        'province_id',
        'division_id',
        'user_id',
        'logid',
        'status',
        'effective_timestamp',
        'timestamp',
        'last_updated',
    ];
}
