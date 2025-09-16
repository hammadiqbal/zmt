<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestigationTracking extends Model
{
    use HasFactory;
    protected $table = 'investigation_tracking';
    public $timestamps = false;
    protected $fillable = ['investigation_id', 'age', 'investigation_confirmation_datetime', 'confirmation_remarks', 'reporting_datetime', 'report', 'report_remarks',
    'user_id', 'logid', 'last_updated','timestamp'];
}
