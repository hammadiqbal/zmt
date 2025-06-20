<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequsitionForOtherTransaction extends Model
{
    use HasFactory;

    protected $table = 'requisition_other_transaction';
    public $timestamps = false;
    protected $fillable = ['code','org_id', 'site_id', 'transaction_type_id','inv_location_id',
     'generic_id', 'qty', 'remarks', 'status', 'user_id','logid',
    'effective_timestamp', 'timestamp', 'last_updated'];
}
