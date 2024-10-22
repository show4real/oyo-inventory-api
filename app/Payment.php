<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Invoice;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;
    protected $appends = [
        'invoice_num'
    ];
    public function getInvoiceNumAttribute()
    {
        $invoice= Invoice::where('id',$this->invoice_id)->first();
        return $invoice->invoice_no;
    }

    public function scopeClient($query, $filter){
        if($filter != null){
         return $query->where("client_id",$filter)->latest();
        }
    }

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
}
