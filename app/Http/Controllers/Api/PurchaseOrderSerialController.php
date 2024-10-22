<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\PurchaseOrderSerial;

class PurchaseOrderSerialController extends Controller
{
    public function getSerials(Request $request){
        $purchase_order_serials= PurchaseOrderSerial::where('purchase_order_id', $request->purchase_order_id)->where('returned_at', null)->get();
        return response()->json(compact('purchase_order_serials'));
    }
}
