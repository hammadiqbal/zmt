<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientAttachments extends Model
{
    use HasFactory;
    protected $table = 'patient_attachments';
    public $timestamps = false;
    protected $fillable = [
        'mr_code',
        'patient_age',
        'service_id',
        'service_mode_id',
        'billing_cc',
        'emp_id',
        'description',
        'date',
        'attachments',
        'user_id',
        'logid',
        'timestamp',
        'last_updated',
    ];
}
