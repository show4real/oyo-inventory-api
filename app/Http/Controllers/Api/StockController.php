<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Stock;
use App\Supplier;
use App\Branch;
use App\Product;
use App\PurchaseOrder;
use DB;
use App\Attribute;
use App\StockSerialNo;
use App\PurchaseOrderSerial;
use Carbon\Carbon;

class StockController extends Controller
{
    
    private function totalStocks($order){
        if($order){
            $total_stock=Stock::where('product_id',$order)
        ->select(DB::raw('sum(stock_quantity * unit_price) as total'))->get();
        }else{
            $total_stock=Stock::select(DB::raw('sum(stock_quantity * unit_price) as total'))->get();
        }
        return $total_stock;

    }


    public function stocks(Request $request)
    {
        $stocks=Stock::where('branch_id', $request->branch_id)->search($request->search)
        ->product($request->order)
        ->with('order')
        ->latest()
        ->paginate($request->rows, ['*'], 'page', $request->page);

        $branch = Branch::where('id',$request->branch_id)->first()->name;
        $product = $request->order !== null ? Product::where('id', $request->order)->first()->name : '';

        $suppliers=Supplier::select('id','name')->paginate($request->rows, ['*'], 'page', $request->page);
        
        $products=Product::select('id','name')->paginate($request->rows, ['*'], 'page', $request->page);
      
       
        return response()->json(compact('stocks','products','suppliers','branch','product'));
       
    }

    public function branchStocks(Request $request)
    {
        $user= auth()->user();
        if($user->admin === 1){
            $stocks=Stock::search($request->search)
            ->product($request->order)
            ->with('order')
            ->with('serials')
            ->latest()
            ->paginate($request->rows, ['*'], 'page', $request->page);
        } else {
            $stocks=Stock::where('branch_id', $user->branch_id)->search($request->search)
            ->product($request->order)
            ->with('order')
            ->with('serials')
            ->latest()
            ->paginate($request->rows, ['*'], 'page', $request->page);
        }
      
        $suppliers=Supplier::select('id','name')->paginate($request->rows, ['*'], 'page', $request->page);
        $products=Product::select('id','name')->paginate($request->rows, ['*'], 'page', $request->page);
        $branches=Branch::select('id','name')->paginate($request->rows, ['*'], 'page', $request->page);
       
       
        return response()->json(compact('stocks','products','suppliers','branches'));
       
    }

    public function show(Request $request, $order){
        $user= auth()->user();
        if( $user->admin === 1 ){
            $stock = Stock::where('id', $order)->with('order')->firstOrFail();
        } else {
            $stock= Stock::where('id', $order)
            ->where('branch_id', $user->branch_id)->first();
        }

        $serials= StockSerialNo::where('stock_id', $stock->id)->paginate($request->rows, ['*'], 'page', $request->page);
        $attributes=Attribute::where('product_id',$request->product_id)
        ->with('attributevalues')->get();

        return response()->json(compact('stock','attributes', 'serials'));
        
    }

    public function saveSerial(Request $request){
        $delete_stock= StockSerialNo::where('stock_id', $request->id)->delete();

        for ($i = 0; $i < count($request->serial_no); $i++) {
            
            $serial = StockSerialNo::updateOrCreate([
                'id' => $request->serial_id[$i],
                       
            ],[ 'stock_id' => $request->stock_id[$i],
            'serial_no' =>  $request->serial_no[$i]]);     
        }
        
    }

    public function editSerial(Request $request){
        $stock_serial = StockSerialNo::where('id', $request->id)->first();
        $check_serial = StockSerialNo::where('stock_id', $stock_serial->stock_id)
        ->where('serial_no', $request->serial_no)->exists();
        if($check_serial)
        return response()->json("Serial No existed", 422);
        $stock_serial->serial_no = $request->serial_no;
        $stock_serial->save();
        return response()->json(compact('stock_serial'));
    }

    public function returnStock(Request $request){
        
        $stock= Stock::find($request->stock_id);

        if($request->selectedSerials){
            foreach($request->selectedSerials as $serial_ids){
                $stock_serial_ids[]=$serial_ids['value'];
                $serial_nos[] = $serial_ids['label'];
            }
    
            $quantity_returned = count($stock_serial_ids);
            $stock->quantity_returned = $stock->quantity_returned+$quantity_returned;
            $stock->save();
    
            $purchase_order = PurchaseOrder::find($stock->purchase_order_id);
            $purchase_order->quantity_moved = $purchase_order->quantity_moved - $quantity_returned;
            $purchase_order->save();
    
            $purchase_order_serial = PurchaseOrderSerial::whereIn('serial_no', $serial_nos)
            ->update(['moved_at' => null, 'branch_moved_to' => null]);
            $stock_serial= StockSerialNo::whereIn('id', $stock_serial_ids)->delete();

        } else {
            
            $stock->quantity_returned = $stock->quantity_returned+$request->quantity_returned;
            $stock->save();
        }
        
        
        return response()->json(compact('stock_serial_ids'));
    }

    public function serials(Request $request){
        $stock_serials= StockSerialNo::where('stock_id', $request->stock_id)->get();
        return response()->json(compact('stock_serials'));
    }
}
