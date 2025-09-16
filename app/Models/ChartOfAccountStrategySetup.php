<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccountStrategySetup extends Model
{
    use HasFactory;
    protected $table = 'account_strategy_setup';
    public $timestamps = false;
    protected $fillable = [
        'org_id',
        'account_strategy_id',
        'status',
        'user_id',
        'logid',
        'effective_timestamp',
        'timestamp',
        'last_updated',
    ];
}
