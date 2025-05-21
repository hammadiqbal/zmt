<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccountStrategy extends Model
{
    use HasFactory;
    protected $table = 'account_strategy';
    public $timestamps = false;
    protected $fillable = [
        'name',
        'remarks',
        'level',
        'status',
        'user_id',
        'logid',
        'effective_timestamp',
        'timestamp',
        'last_updated',
    ];
}
