<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExpiredStock extends Model
{
    protected $table = "expired_stocks";

    protected $fillable = [
        "stock_id",
        "removed_by",
        "branch_id",
        "quantity",
        "expiry_date",
        "organization_id"
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class, 'stock_id');
    }
}
