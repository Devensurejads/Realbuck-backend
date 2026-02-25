<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Investor extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'investor';
    public $timestamps = true;

    protected $hidden = ['password'];
}