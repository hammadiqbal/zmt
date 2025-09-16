<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Model;

class Users extends Model implements Authenticatable
{
    use HasFactory;
    protected $table = 'user';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'org_id',
        'is_employee',
        'site_enabled',
        'emp_id',
        'user_id',
        'logid',
        'status',
        'password_reset_token',
        'effective_timestamp',
        'timestamp',
        'last_updated'
    ];

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value)
    {
    }

    public function getRememberTokenName()
    {
        return null;
    }
}
