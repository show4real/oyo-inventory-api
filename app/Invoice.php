<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Client;
use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Payment;


class Invoice extends Model
{
    use SoftDeletes;

    protected $appends = [
        'client_name', 'cashier_name','total_payment','total_balance'
    ];
    public function getClientNameAttribute()
    {
       if($this->client_id){
            $client= Client::where('id',$this->client_id)->first();
            return $client->name;
       }
    }

  

    public function getCashierNameAttribute()
    {
       if($this->cashier_id){
            $cashier= User::where('id',$this->cashier_id)->first();
            return $cashier->firstname.' '.$cashier->lastname;
       }
    }

    public function client(){
        return $this->belongsTo('App\Client', 'client_id');
    }

    

    public function scopeOrder($query, $filter){
        if($filter != null){
         return $query->where("client_id",$filter)->latest();
        }
    }

    public function scopeCurrency($query, $filter){
        if($filter != null){
         return $query->where("currency",$filter)->latest();
        }
    }

    public function getTotalBalanceAttribute(){
        if($this->id){
            $payments=Payment::where('invoice_id',$this->id)->get();
            $total=0;
            foreach($payments as $payment){
                $total+= $payment->amount_paid;
            }
            return $this->amount-$total;
        }
    }

    public function getTotalPaymentAttribute(){
        if($this->id){
            $payments=Payment::where('invoice_id',$this->id)->get();
            $total=0;
            foreach($payments as $payment){
                $total+= $payment->amount_paid;

            }
            return $total;

            
        }
    }

    public function scopeCashier($query, $filter){
        if($filter != null){
         return $query->where("cashier_id",$filter)->latest();
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
            $requestData = ['invoice_no', 'client_id'];
            $clientData = ['name','address'];
        
            return $query->where(function ($q) use ($requestData, $searchQuery, $clientData) {
                $q->where(function ($qq) use ($requestData, $searchQuery) {
                    foreach ($requestData as $field) {
                        $qq->orWhere($field, 'like', "%{$searchQuery}%");
                    }
                })->orWhere(function ($qq) use ($clientData, $searchQuery) {
                        foreach ($clientData as $field) {
                            $qq->orWhereHas('client', function ($qqq) use ($clientData, $searchQuery, $field) {
                                $qqq->where($field, 'like', "%{$searchQuery}%");
                            });
                        }
                });
            });
        });

    }

    public function payments(){
        return $this->hasMany('App\Payment', 'invoice_id');
    }


    public function scopeRemove() {
        $this->payments()->delete();
        parent::delete();
    }



}
