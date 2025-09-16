<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ICDCoding extends Model
{
    use HasFactory;
    protected $table = 'icd_code';
    public $timestamps = false;
    protected $fillable = [
        'description',
        'code',
        'type',
        'user_id',
        'logid',
        'status',
        'effective_timestamp',
        'timestamp',
        'last_updated',
    ];
}
