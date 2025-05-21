<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modules extends Model
{
    use HasFactory;
    protected $table = 'modules';
    public $timestamps = false;
    protected $fillable = ['name', 'parent', 'user_id', 'logid', 'timestamp','last_updated'];
}
