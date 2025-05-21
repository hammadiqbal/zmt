<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonorRegistration extends Model
{
    use HasFactory;
    protected $table = 'donors';
    public $timestamps = false;
    protected $fillable = [
        'org_id',
        'type',
        'corporate_name',
        'person_name',
        'person_email',
        'person_cell',
        'person_landline',
        'address',
        'remarks',
        'status',
        'user_id',
        'logid',
        'effective_timestamp',
        'timestamp',
        'last_updated',
    ];
}
