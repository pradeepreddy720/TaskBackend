<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class resetPassword extends Model
{
    protected $fillable = ['email', 'code'];
}
