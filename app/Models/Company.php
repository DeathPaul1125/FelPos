<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    // Fillable fields for mass assignment

    protected $fillable = [
        'name',
        'admin',
        'email',
        'phone',
        'address',
        'website',
        'logo',
        'nit',
        'user_fel',
        'password_fel',
        'token_fel',
        'Produccion'
    ];
}
