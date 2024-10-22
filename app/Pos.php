<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\PurchaseOrder;
use App\Product;
use App\Supplier;
use App\StockSerialNo;
use App\CompanySettings;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pos extends Model
{
    use SoftDeletes;
    protected $table = "pos";
    protected $appends = ["cashier_name","supplier_name","cost", "selling_price","product_name","sold_serials"];
    protected $hidden = ["product"];
    protected $fillable = ['purchase_order_id',
        'qty_sold', 'transaction_id', 'product_id',
         'payment_mode', 'cashier_id','serials'];
    protected $casts = ['qty_sold'=>'integer','product_id'=>'integer', 'serials' => 'array'];

    public function scopeSearch($query, $filter)
    {
        $query->when($filter != null, function ($query) use ($filter) {
            $searchQuery = trim($filter);
            $requestData = ['cashier_id', 'transaction_id'];
            $userData = ['firstname'];
        
            return $query->where(function ($q) use ($requestData, $searchQuery, $userData) {
                $q->where(function ($qq) use ($requestData, $searchQuery) {
                    foreach ($requestData as $field) {
                        $qq->orWhere($field, 'like', "%{$searchQuery}%");
                    }

                })
                    ->orWhere(function ($qq) use ($userData, $searchQuery) {
                        foreach ($userData as $field) {
                            $qq->orWhereHas('user', function ($qqq) use ($userData, $searchQuery, $field) {
                                $qqq->where($field, 'like', "%{$searchQuery}%");
                            });
                        }

                    

                });
            });
        });

    }

    public function getCashierNameAttribute()
    {
        $user = User::where('id', $this->cashier_id)->first();

        return $user->firstname . ' ' . $user->lastname;
    }

    public function getSellingPriceAttribute()
    {
        $purchase_order = PurchaseOrder::where('id', $this->purchase_order_id)->first();
        if($purchase_order){
            return $purchase_order->unit_selling_price;
        }

    }

    public function getProductNameAttribute()
    {
        $product_id = PurchaseOrder::where('tracking_id', $this->purchase_order_id)->first();
        if($product_id){
            $product= Product::where('id',$product_id->product_id)->first();
            if($product){
                return $product->name;
            }
        }
    }

    public function getBalanceAttribute(){
        if($this->transaction_id){
            $balance = Invoice::where('transaction_id', $this->transaction_id)->first();
            return $balance->balance;
        }
      
    }
    
    public function getSoldSerialsAttribute(){
        if($this->serials){
            $serials = StockSerialNo::whereIn('id',$this->serials)->select('serial_no','sold_at')->get();
            return $serials;

        }

    }

    public function scopeOrder($query, $filter)
    {
        if ($filter != null) {
            return $query->where("channel", $filter)->latest();
        }
    }

   


    public function getSupplierNameAttribute(){
      
        $supplier= PurchaseOrder::where('id', $this->purchase_order_id)->first();
        if($supplier){
            $name= Supplier::where('id', $supplier->id)->first();
            if($name){
                return $name->name;
            }
           
        }
    }

    public function scopeProduct($query, $filter)
    {
        
        if ($filter != null) {
          
            return $query->where("product_id", $filter)->latest();
        }
    }

    public function scopeEmployee($query, $filter)
    {
        if ($filter != null) {
            return $query->where("cashier_id", $filter)->latest();
        }
    }

  




    public function user(){
        return $this->belongsTo('App\User', 'cashier_id');
    }

    public function stock(){
        return $this->belongsTo('App\Stock', 'stock_id');
    }

    public function order(){
        return $this->belongsTo('App\PurchaseOrder', 'purchase_order_id');
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

    public function getCostAttribute(){
        if($this->purchase_order_id){
            $cost= PurchaseOrder::where('tracking_id', $this->purchase_order_id)->first();
            if($cost){
                return $cost->unit_selling_price;
            }
            
        }
    }

}
