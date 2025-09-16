<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialPayrollDeduction extends Model
{
    use HasFactory;
    protected $table = 'finance_payroll_deductions';
    public $timestamps = false;
    protected $fillable = [
        'name',
        'org_id',
        'status',
        'user_id',
        'logid',
        'effective_timestamp',
        'timestamp',
        'last_updated',
    ];
}
