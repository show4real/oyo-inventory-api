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

    public function scopeExpiryDateBetween($query, $start_date = null, $end_date = null)
    {
        if ($start_date && $end_date) {
            return $query->whereBetween('expiry_date', [$start_date, $end_date]);
        }

        if ($start_date) {
            return $query->whereDate('expiry_date', '>=', $start_date);
        }

        if ($end_date) {
            return $query->whereDate('expiry_date', '<=', $end_date);
        }

        return $query;
    }
}
