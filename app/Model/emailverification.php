<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class emailverification extends Model
{
    protected $fillable = ['userId', 'verification','status'];
}
