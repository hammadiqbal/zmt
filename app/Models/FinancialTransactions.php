<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialTransactions extends Model
{
    use HasFactory;
    protected $table = 'finance_transactions';
    public $timestamps = false;
    protected $fillable = [
        'org_id',
        'site_id',
        'transaction_type_id',
        'payment_option',
        'payment_option_detail',
        'amount',
        'discount',
        'debit',
        'credit',
        'remarks',
        'status',
        'user_id',
        'logid',
        'effective_timestamp',
        'timestamp',
        'last_updated',
    ];
}
