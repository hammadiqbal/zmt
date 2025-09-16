<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialTransactionTypes extends Model
{
    use HasFactory;

    protected $table = 'finance_transaction_type';
    public $timestamps = false;
    protected $fillable = [
        'name',
        'activity',
        'transaction_source_id',
        'transaction_destination_id	',
        'debit_account',
        'credit_account',
        'ledger_id',
        'amount_editable',
        'amount_ceiling',
        'discount_allowed',
        'org_id',
        'status',
        'user_id',
        'logid',
        'effective_timestamp',
        'timestamp',
        'last_updated',
    ];
}
