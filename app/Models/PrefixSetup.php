<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrefixSetup extends Model
{
    use HasFactory;
    protected $table = 'prefix';
    public $timestamps = false;
    protected $fillable = ['name', 'user_id', 'logid', 'status',
    'effective_timestamp', 'timestamp','last_updated'];
} 