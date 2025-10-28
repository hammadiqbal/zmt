<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    use HasFactory;
    protected $table = 'logs';
    public $timestamps = false;
    protected $fillable = [
        'module',
        'event',
        'user_id',
        'record_id',
        'previous_data',
        'new_data',
        'summary',
        'timestamp'
    ];
}
