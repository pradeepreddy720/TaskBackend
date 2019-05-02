<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class favourite extends Model
{
    protected $fillable = ['userId', 'companyId'];
}
