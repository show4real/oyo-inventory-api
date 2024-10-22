<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $table="productimages";
    protected $fillable=['product_id','url'];
    //
}
