<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransactionActivity extends Model
{
    use HasFactory;
    protected $table = 'inventory_transaction_activity';
    public $timestamps = false;
    protected $fillable = [
        'org_id',
        'name',
        'status',
        'user_id',
        'logid',
        'effective_timestamp',
        'timestamp',
        'last_updated',
    ];
}
