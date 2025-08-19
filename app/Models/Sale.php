<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'total',
        'cash',
        'change',
        'payment_method',
        'status',
        'user_id',
    ];
}
