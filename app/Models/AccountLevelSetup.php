<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountLevelSetup extends Model
{
    use HasFactory;
    protected $table = 'account_level_setup';
    public $timestamps = false;
    protected $fillable = [
        'account_strategy_setup_id',
        'name',
        'account_level',
        'parent_level1_id',
        'parent_level2_id',
        'parent_level3_id',
        'parent_level4_id',
        'parent_level5_id',
        'parent_level6_id',
        'parent_level7_id',
        'parent_level8_id',
        'parent_level9_id',
        'parent_level10_id',
        'status',
        'user_id',
        'logid',
        'effective_timestamp',
        'timestamp',
        'last_updated',
    ];
}
