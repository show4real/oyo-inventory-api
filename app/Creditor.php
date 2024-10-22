<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Creditor extends Model
{
    protected $table = 'creditors';
    protected $appends = ['supplier_name','product_name','total_balance','total_payment','tracking'];


    public function product(){
        return $this->belongsTo('App\Product', 'product_id');
    }

    public function supplier(){
        return $this->belongsTo('App\Supplier', 'supplier_id');
    }



    public function scopeProduct($query, $filter){
        if($filter != null){
         return $query->where("product_id",$filter)->latest();
        }
    }


     

     public function scopeFilter1($query, $filter){
        if($filter != null){
         return $query->where("created_at",'>',$filter)->latest();
        }
     }

     public function scopeFilter2($query, $filter){
        if($filter != null){
         return $query->where("created_at",'<',$filter)->latest();
        }
     }



    public function scopeSearch($query, $filter)
    {
        $query->when($filter != null, function ($query) use ($filter) {
            $searchQuery = trim($filter);
            $requestData = ['amount', 'product_id'];
            $productData = ['name'];
            $supplierData = ['name'];
        
            return $query->where(function ($q) use ($requestData, $searchQuery, $productData, $supplierData) {
                $q->where(function ($qq) use ($requestData, $searchQuery) {
                    foreach ($requestData as $field) {
                        $qq->orWhere($field, 'like', "%{$searchQuery}%");
                    }
                })->orWhere(function ($qq) use ($productData, $searchQuery) {
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

    public function payments(){
        return $this->hasMany('App\CreditorPayment', 'creditor_id');
    }

    public function getSupplierNameAttribute()
    {
        if ($this->supplier_id) {
            $supplier = Supplier::where('id',$this->supplier_id)->first()->name;
            return $supplier;
        }
    }

    public function getProductNameAttribute()
    {
        if ($this->product_id) {
            $product = Product::where('id',$this->product_id)->first()->name;
            return $product;
        }
    }

    public function getTrackingAttribute()
    {
        if ($this->purchase_order_id) {
            $tracking = PurchaseOrder::where('id',$this->purchase_order_id)->first()->tracking_id;
            return $tracking;
        }
    }

    public function getTotalBalanceAttribute(){
        if($this->id){
            $payments=CreditorPayment::where('creditor_id',$this->id)->get();
            $total=0;
            foreach($payments as $payment){
                $total+= $payment->amount_paid;
            }
            return $this->amount-$total;
        }
    }

    public function getTotalPaymentAttribute(){
        if($this->id){
            $payments=CreditorPayment::where('creditor_id',$this->id)->get();
            $total=0;
            foreach($payments as $payment){
                $total+= $payment->amount_paid;

            }
            return $total;

            
        }
    }

}
