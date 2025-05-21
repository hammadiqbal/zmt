<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorRegistration extends Model
{
    use HasFactory;
    protected $table = 'vendor';
    public $timestamps = false;
    protected $fillable = ['name', 'address', 'org_id', 'person_name',
    'person_email', 'cell_no', 'landline_no', 'remarks', 'status', 'user_id', 'logid',
    'effective_timestamp', 'last_updated','timestamp'];
}
