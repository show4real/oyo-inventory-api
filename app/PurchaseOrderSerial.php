<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Branch;

class PurchaseOrderSerial extends Model
{
    protected $fillable = ['purchase_order_id', 'moved_at','branch_moved_to','serial_no'];

    protected $appends = ['branch_name'];
    public function scopeGetPurchaseOrderSerials($query,$request){
        return $query->where('purchase_order_id', $request->id)->paginate(10);
    }

    public function getBranchNameAttribute(){
        $branch = Branch::where('id',$this->branch_moved_to)->first();
        if($branch)
        return $branch->name;
    }

}
