<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CreditorPayment extends Model
{
    protected $table = 'creditors_payments';
    protected $appends = ["supplier_name","paid_by"];

    public function scopeStart($query, $filter){
        if($filter != null){
         return $query->where("created_at",'>',$filter)->latest();
        }
     }

     public function scopeEnd($query, $filter){
        if($filter != null){
         return $query->where("created_at",'<',$filter)->latest();
        }
     }

     public function getSupplierNameAttribute(){
         if($this->creditor_id){
             $creditor = Creditor::where('id', $this->creditor_id)->first();

             if($creditor){
            return $creditor->supplier_name;
             }
           

         }
     }
     public function getPaidByAttribute(){
         if($this->created_by){
             $payer= User::where('id', $this->created_by)->first()->name;
             return $payer;
         }
     }
}
