<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'barcode',
        'description',
        'cost',
        'price',
        'stock',
        'min_stock',
        'image',
        'category_id'
    ];
}
