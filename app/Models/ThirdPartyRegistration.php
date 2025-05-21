<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThirdPartyRegistration extends Model
{
    use HasFactory;
    protected $table = 'third_party';
    public $timestamps = false;
    protected $fillable = ['org_id', 'type', 'category', 'corporate_name', 'person_name',  
    'person_email', 'person_cell', 'landline', 'address', 'remarks', 'status', 'user_id', 'logid',
    'effective_timestamp', 'last_updated','timestamp'];
}
