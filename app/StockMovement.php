<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{


    protected $fillable = [
        'from_stock_id',
        'to_stock_id',
        'from_branch_id',
        'to_branch_id',
        'product_id',
        'quantity',
        'moved_by',
        'reason',
        'organization_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    
    public function fromStock()
    {
        return $this->belongsTo(Stock::class, 'from_stock_id');
    }

    public function toStock()
    {
        return $this->belongsTo(Stock::class, 'to_stock_id');
    }

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function movedBy()
    {
        return $this->belongsTo(User::class, 'moved_by');
    }

    
}
