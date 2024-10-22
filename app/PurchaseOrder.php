<?php

namespace App;

use App\ProductImage;
use App\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Branch;

class PurchaseOrder extends Model
{
    use SoftDeletes;
    protected $table = "purchase_order";
    protected $appends = ["product_name", "product_description", "supplier_name", "profit", "new_stock_qty", "branch_name", "status", "in_stock", 'product_image'];
    protected $hidden = ["product"];
    protected $fillable = ['quantity_sold'];
    protected $dates = [ 'created_at' ];
    protected $casts = [
        'stock_qty' => 'integer',
        'unit_price'  => 'integer',
        'quantity'  => 'integer',
        'quantity_sold'  => 'integer',
        'quantity_moved'  => 'integer',
        'quantity_returned' => 'integer',
         'stock_quantity' => 'integer'
        
    ];

    public function scopeSearch($query, $filter)
    {
        $query->when($filter != null, function ($query) use ($filter) {
            $searchQuery = trim($filter);
            $requestData = ['tracking_id', 'stock_quantity', 'unit_price', 'barcode'];
            $productData = ['name'];
            $supplierData = ['name'];
            return $query->where(function ($q) use ($requestData, $searchQuery, $productData, $supplierData) {
                $q->where(function ($qq) use ($requestData, $searchQuery) {
                    foreach ($requestData as $field) {
                        $qq->orWhere($field, 'like', "%{$searchQuery}%");
                    }

                })
                    ->orWhere(function ($qq) use ($productData, $searchQuery) {
                        foreach ($productData as $field) {
                            $qq->orWhereHas('product', function ($qqq) use ($productData, $searchQuery, $field) {
                                $qqq->where($field, 'like', "%{$searchQuery}%");
                            });
                        }

                    })->orWhere(function ($qq) use ($supplierData, $searchQuery) {
                    foreach ($supplierData as $field) {
                        $qq->orWhereHas('supplier', function ($qqq) use ($supplierData, $searchQuery, $field) {
                            $qqq->where($field, 'like', "%{$searchQuery}%");
                        });
                    }

                });
            });
        });

    }

    public function getInstockAttribute()
    {
        $sold = $this->quantity_sold;
        $return = $this->quantity_returned;
        $moved = $this->quantity_moved;
        $stock = $this->stock_quantity;
        $qty = $sold + $return + $moved;
        $instock = $stock - $qty;
        return $instock;
    }

    public function getTotalSalesAttribute()
    {
        $sum += $this->quantity_sold;
        return $sum;
    }

    public function getProductImageAttribute()
    {
        if ($this->product_id) {
            $pImage = ProductImage::where('product_id', $this->product_id)
                ->select('url')->first();
            return $pImage;
        }
    }

    public function getNewStockQtyAttribute()
    {
        if ($this->quantity_returned) {
            $new_qty = $this->stock_quantity - $this->quantity_returned;
            return $new_qty;
        } else {
            return $this->stock_quantity;
        }
    }

    public function getProfitAttribute()
    {
        $selling_price = $this->quantity_sold * $this->unit_selling_price;
        $cost_price = $this->quantity_sold * $this->unit_price;
        $profit = $selling_price - $cost_price;
        return $profit;
    }

    public function getStatusAttribute()
    {
        //dd($this->confirmed_at);
        if ($this->confirmed_at
            && $this->received_at
            && !$this->rejected_at) {
            return 'Confirmed';
        }
        if (!$this->confirmed_at
            && !$this->received_at
            && !$this->rejected_at) {
            return 'Pending';
        }
        if ($this->rejected_at) {
            return 'Rejected';
        }
    }

    public function product()
    {
        return $this->belongsTo('App\Product', 'product_id')
            ->select('id', 'name', 'description');
    }

    public function branch()
    {
        return $this->belongsTo('App\Branch', 'branch_id')
            ->select('id', 'name');
    }

    public function getBranchNameAttribute(){
        $branch = Branch::where('id',$this->branch_id)->first();
        if($branch)
        return $branch->name;
    }


    public function getSupplierNameAttribute()
    {
        if ($this->supplier) {
            return $this->supplier->name;
        }
    }

    public function supplier()
    {
        return $this->belongsTo('App\Supplier', 'supplier_id')
            ->select('id', 'name', 'supplier_id');
    }

    public function serials(){
        return $this->hasMany('App\PurchaseOrderSerial', 'purchase_order_id');
    }



    public function getProductNameAttribute()
    {
        if ($this->product) {
            return $this->product->name;
        }
    }

     public function getProductDescriptionAttribute()
    {
        if ($this->product) {
            return $this->product->description;
        }
    }

    public function scopeOrder($query, $filter)
    {
        if ($filter != null) {
            return $query->where("product_id", $filter)->latest();
        }
    }

    public function scopeBranch($query, $filter)
    {
        $query->when($filter != null, function ($query) use ($filter) {
            return $query->where('branch_id', $filter);
        });
    }

    public function scopeFilter1($query, $filter)
    {
        if ($filter != null) {
            return $query->where("created_at", '>=', $filter)->latest();
        }
    }



    public function scopeFilter2($query, $filter)
    {
        if ($filter != null) {
            return $query->where("created_at", '<=', $filter)->latest();
        }
    }

    public function scopeGetSales($query, $request){
        $purchase =  $query->filter1($request->fromdate)
        ->filter2($request->todate)
        ->search($request->search)
        ->with('supplier')
        ->order($request->order)->get();
        $total_purchase=0;
        foreach($purchase as $sale){
            
            $purchase=$sale['unit_price']* $sale['stock_quantity'];
             $total_purchase+=$purchase;
             
        }
        return $total_purchase;
    }

}
