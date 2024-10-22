<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\PurchaseOrder;

class Stock extends Model
{
    use SoftDeletes;
    protected $appends=["supplier_id","product_name",'qr',"profit","tracking","new_stock_qty","branch_name","status","in_stock",'product_image'];

    
    protected $hidden = ['product','branch'];
    protected $fillable = ['stock_quantity','purchase_order_id','branch_id','product_id','supplier_id'];

    public function order(){
        return $this->belongsTo('App\PurchaseOrder', 'purchase_order_id');
    }

    
   


    public function scopeSearch($query, $filter)
    {
    	$query->when($filter!=null, function ($query) use($filter) {
        $searchQuery = trim($filter);
        $requestData = [ 'branch_id'];
        $productData = ['name'];
        $orderData=['tracking_id','supplier_id', 'stock_quantity','unit_price','barcode'];
        $branchData=['name'];
        return $query->where(function($q) use($requestData, $searchQuery, $productData,$orderData,$branchData) {
        $q->where(function($qq) use($requestData, $searchQuery) {
            foreach ($requestData as $field)
            $qq->orWhere($field, 'like', "%{$searchQuery}%");
            })
            ->orWhere(function($qq) use($productData, $searchQuery) {
            foreach ($productData as $field)
                $qq->orWhereHas('product', function($qqq) use($productData, $searchQuery, $field) {
                    $qqq->where($field, 'like', "%{$searchQuery}%");
                });
            })->orWhere(function($qq) use($orderData, $searchQuery) {
                foreach ($orderData as $field)
                    $qq->orWhereHas('order', function($qqq) use($orderData, $searchQuery, $field) {
                        $qqq->where($field, 'like', "%{$searchQuery}%");
                    });
                })->orWhere(function($qq) use($branchData, $searchQuery) {
                    foreach ($branchData as $field)
                        $qq->orWhereHas('branch', function($qqq) use($branchData, $searchQuery, $field) {
                            $qqq->where($field, 'like', "%{$searchQuery}%");
                        });
                    });
        });
        });
      
    }

    public function getInstockAttribute(){
        $sold=$this->quantity_sold;
        $return=$this->quantity_returned;
        $stock=$this->stock_quantity;
        $qty=$sold+$return;
        $instock=$stock-$qty;
        return $instock;
    }


    public function getSupplierIdAttribute(){
        $PurchaseOrder = PurchaseOrder::where('id', $this->purchase_order_id)->first();
        if($PurchaseOrder){
            return $PurchaseOrder->supplier_id;
        }
    }

    public function getQrAttribute(){
        
        $return=$this->quantity_returned;
        
        return $return;
    }


    public function getTotalSalesAttribute(){
        $sum+=$this->quantity_sold;
        return $sum;
    }

  

    public function getProductImageAttribute(){
        if($this->product_id){
            $pImage=ProductImage::where('product_id',$this->product_id)
            ->select('url')->first();
            return $pImage;
        }
    }

    public function getNewStockQtyAttribute(){
        if($this->quantity_returned){
            $new_qty= $this->stock_quantity-$this->quantity_returned;
            return $new_qty;
        }else{
            return $this->stock_quantity;
        }
    }

    public function getTrackingAttribute(){
        if($this->purchase_order_id){
            $tracking = PurchaseOrder::where('id', $this->purchase_order_id)->first();
            if($tracking){
                  return $tracking->tracking_id;
            }
          
        }
    }

    public function getProfitAttribute(){
        $selling_price=$this->quantity_sold*$this->unit_selling_price;
        $cost_price=$this->quantity_sold*$this->unit_price;
        $profit= $selling_price - $cost_price;
        return $profit;
    }


    public function getStatusAttribute()
    {
        //dd($this->confirmed_at);
      if($this->confirmed_at
        &&$this->received_at
        &&!$this->rejected_at){
        return 'Confirmed';
      }
      if(!$this->confirmed_at
        &&!$this->received_at
        &&!$this->rejected_at){
        return 'Pending';
      }
      if($this->rejected_at){
        return 'Rejected';
      }
    }

	public function product(){
        return $this->belongsTo('App\Product', 'product_id')
        ->select('id','name');
    }

    public function branch(){
        return $this->belongsTo('App\Branch', 'branch_id')
        ->select('id','name');
    }

    public function getBranchNameAttribute()
    {
        if($this->branch){
            return $this->branch->name;
        }
    }

    public function serials(){
        return $this->hasMany('App\StockSerialNo','stock_id')->where('sold_at', null);
    }

    
    
	public function getProductNameAttribute()
    {
        if($this->product){
            return $this->product->name;
        }
    }

    

   

    public function scopeProduct($query, $filter){
       if($filter != null){
        return $query->where("product_id",$filter)->latest();
       }
    }


    

   

    public function scopeBranch($query, $filter)
    {
  		$query->when($filter!=null, function ($query) use($filter) {
        return $query->where('branch_id', $filter);
  		});
    }

    public function scopeStartdate($query, $filter){
        if($filter != null){
           return  $query->whereDate('created_at', '>', $filter);
        }
    }

    public function scopeEnddate($query, $filter){
        if($filter != null){
           return  $query->whereDate('created_at', '<', $filter);
        }
    }


}
