<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SavedTransactionItem extends Model
{
    protected $table = 'saved_transaction_items';

    protected $fillable = [
        'saved_transaction_id',
        'stock_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    public function savedTransaction()
    {
        return $this->belongsTo(SavedTransaction::class, 'saved_transaction_id');
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class, 'stock_id');
    }
}