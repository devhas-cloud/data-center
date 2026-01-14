<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory;

    protected $table = 'tbl_user';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'address',
        'role',
        'level',
        'date_expired',
        'api_key',
        'access'
    ];

    protected $hidden = [
        'password'
    ];
}
