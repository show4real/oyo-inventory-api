<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Barcode extends Model
{
    protected $fillable = ['name','organization_id'];

    public function orderItems()
    {
        return $this->hasMany(PurchaseOrder::class, 'barcode', 'name');
    }
}
