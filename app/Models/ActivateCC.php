<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivateCC extends Model
{
    use HasFactory;
    protected $table = 'activated_cc';
    public $timestamps = false;
    protected $fillable = [
        'org_id',
        'site_id',
        'cc_id',
        'status',
        'user_id',
        'logid',
        'effective_timestamp',
        'timestamp',
        'last_updated',
    ];
}
