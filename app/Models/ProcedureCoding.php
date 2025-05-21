<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcedureCoding extends Model
{
    use HasFactory;
    protected $table = 'procedure_coding';
    public $timestamps = false;
    protected $fillable = ['icd_id', 'org_id', 'service_id', 'user_id',
    'logid', 'timestamp','last_updated'];
}
