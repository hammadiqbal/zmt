<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicationRoutes extends Model
{
    use HasFactory;
    protected $table = 'medication_routes';
    public $timestamps = false;
    protected $fillable = ['name', 'org_id', 'status',
    'user_id', 'logid', 'effective_timestamp', 'last_updated','timestamp'];
}
