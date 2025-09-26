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

    protected $appends = [
        "cashier_name",
        "supplier_name",
        "cost",
        "selling_price",
        "product_name",
        "sold_serials",
        "amount",
        "amount_paid"
    ];

    protected $hidden = ["product"];


    protected $fillable = [
        'purchase_order_id',
        'transaction_id',
        'invoice_id',
        'qty_sold',
        'unit_selling_price',
        'serials',
        'supplier_id',
        'stock_id',
        'product_id',
        'cashier_id',
        'payment_mode',
        'channel',
        'edited_by',
        'organization_id',
        'created_at'
    ];
    protected $casts = [
        'qty_sold'=>'integer',
        'product_id'=>'integer',
        'serials' => 'array'
    ];

    public function scopeSearch($query, $filter)
    {
        $query->when($filter != null, function ($query) use ($filter) {
            $searchQuery = trim($filter);
            $requestData = ['cashier_id', 'transaction_id'];
            $userData = ['firstname'];
            $clientData = ['name', 'lastname', 'email', 'phone'];
        
            return $query->where(function ($q) use ($requestData, $searchQuery, $userData, $clientData) {
                $q->where(function ($qq) use ($requestData, $searchQuery) {
                    foreach ($requestData as $field) {
                        $qq->orWhere($field, 'like', "%{$searchQuery}%");
                    }
                })
                ->orWhere(function ($qq) use ($userData, $searchQuery) {
                    foreach ($userData as $field) {
                        $qq->orWhereHas('user', function ($qqq) use ($searchQuery, $field) {
                            $qqq->where($field, 'like', "%{$searchQuery}%");
                        });
                    }
                })
                ->orWhere(function ($qq) use ($clientData, $searchQuery) {
                    foreach ($clientData as $field) {
                        $qq->orWhereHas('invoice.client', function ($qqq) use ($searchQuery, $field) {
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

        if($this->unit_selling_price == null){
            $purchase_order = PurchaseOrder::where('id', $this->purchase_order_id)->first();
            if($purchase_order){
                return $purchase_order->unit_selling_price;
            }
        }

        return $this->unit_selling_price;
        

    }

    public function getProductNameAttribute()
    {
         $product= Product::where('id',$this->product_id)->first();
            if($product){
                return $product->name;
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

    public function scopeBranch($query, $filter)
    {
        if ($filter != null) {
            return $query->whereHas('stock', function ($q) use ($filter) {
                $q->where('branch_id', $filter);
            })->latest();
        }
        return $query;
    }

   


    public function getSupplierNameAttribute(){
      
        $supplier= Supplier::where('id', $this->supplier_id)->first();
            if($supplier){
                return $supplier->name;
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

    public function invoice(){
        return $this->belongsTo('App\Invoice', 'invoice_id');
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

    public function getAmountAttribute(){
         $invoice= Invoice::where('id', $this->invoice_id)->first();

         return $invoice->amount ?? 'N/A';
    }

    public function getAmountPaidAttribute(){
         $invoice= Invoice::where('id', $this->invoice_id)->first();

         return $invoice->amount_paid ?? 'N/A';
    }

}
