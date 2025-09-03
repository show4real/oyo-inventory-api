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
use App\Pos;
use App\Invoice;

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
        
       

        $stocksQuery = Stock::where('organization_id', auth()->user()->organization_id)
            ->where('branch_id', $request->branch_id)
            ->search($request->search)
            ->product($request->order) // assuming this is filtering by product_id
            ->startdate($request->start_date)
            ->enddate($request->end_date)
            ->expiryDate($request->expiry_date)
            ->with('order')
            ->latest();

        $stocks = $stocksQuery->with([
            'movementsFrom.toBranch', 
            'movementsFrom.user',     // who moved FROM
            'movementsTo.fromBranch',
            'movementsTo.user', 
        ])->paginate($request->rows, ['*'], 'page', $request->page);

        $stock_quantity = 0;
        $quantity_sold = 0;
        $instock = 0;

        if ($request->order) {
             
            $stocks_product_details = $stocksQuery->get();

            $stock_quantity = $stocks_product_details->sum('stock_quantity');
            $quantity_sold = $stocks_product_details->sum('quantity_sold');
            $instock = $stock_quantity - $quantity_sold;
        }
        

        $branch = Branch::where('organization_id', auth()->user()->organization_id)->where('id',$request->branch_id)->first()->name;
        $product = $request->order !== null ? Product::where('id', $request->order)->first()->name : '';

        $user = auth()->user();

      
       
        return response()->json(compact('stocks','branch','product','stock_quantity','quantity_sold','instock','user'));
       
    }

    public function branchStocks(Request $request)
    {
        $user= auth()->user();

        $stocks = Stock::where('organization_id', auth()->user()->organization_id)
            ->whereHas('branch', function ($query) {
                $query->where('sell', 1);
            })
            ->search($request->search)
            ->availableStock()
            ->product($request->order)
            ->with(['order', 'serials'])
            ->where('branch_id', $user->branch_id)
            ->latest()
            ->paginate($request->rows, ['*'], 'page', $request->page);
       
       
        return response()->json(compact('stocks','user'));
       
    }


    public function stocks2(Request $request){

        $user= auth()->user();

        $stocks=Stock::where('organization_id', auth()->user()->organization_id)
            ->search($request->search)
            ->whereHas('branch', function ($query) {
                $query->where('sell', 1);
            })
            ->where('branch_id', $user->branch_id)
            ->availableStock()
            ->product($request->order)
            ->with('order')
            ->with('serials')
            ->latest()
            ->paginate($request->rows, ['*'], 'page', $request->page);

        $prev_invoice = Invoice::where('organization_id', auth()->user()->organization_id)->where('id', $request->invoice_id)->first();

        $pos_items = Pos::where('organization_id', auth()->user()->organization_id)->where('invoice_id', $request->invoice_id)
                ->select('stock_id', 'qty_sold','unit_selling_price')
                ->get()
                ->keyBy('stock_id');
       
        
        $stock_ids = $pos_items->pluck('stock_id');
        $sold_stocks = Stock::whereIn('id', $stock_ids)->with('order')->get();


        $sold_stocks->each(function ($stock) use ($pos_items) {
            $stock->quantity = $pos_items[$stock->id]->qty_sold ?? 0;
            $stock->order->unit_selling_price = $pos_items[$stock->id]->unit_selling_price ?? 0;
        });

        $sold_stocks = $sold_stocks->sortBy(function ($stock) use ($stock_ids) {
            return $stock_ids->search($stock->id);
        })->values();

         return response()->json(compact('stocks','sold_stocks','prev_invoice'));
    }

    public function show(Request $request, $order){
        $user= auth()->user();
        if( $user->admin === 1 ){
            $stock = Stock::where('organization_id', auth()->user()->organization_id)->where('id', $order)->with('order')->firstOrFail();
        } else {
            $stock= Stock::where('organization_id', auth()->user()->organization_id)
            ->where('id', $order)
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
        
        $stock= Stock::where('organization_id', auth()->user()->organization_id)->find($request->stock_id);

        if($request->selectedSerials){
            foreach($request->selectedSerials as $serial_ids){
                $stock_serial_ids[]=$serial_ids['value'];
                $serial_nos[] = $serial_ids['label'];
            }
    
            $quantity_returned = count($stock_serial_ids);
            $stock->quantity_returned = $stock->quantity_returned+$quantity_returned;
            $stock->organization_id = auth()->user()->organization_id;
            $stock->save();
    
            $purchase_order = PurchaseOrder::where('organization_id', auth()->user()->organization_id)->find($stock->purchase_order_id);
            $purchase_order->quantity_moved = $purchase_order->quantity_moved - $quantity_returned;
            $purchase_order->save();
    
            $purchase_order_serial = PurchaseOrderSerial::whereIn('serial_no', $serial_nos)
            ->update(['moved_at' => null, 'branch_moved_to' => null]);
            $stock_serial= StockSerialNo::whereIn('id', $stock_serial_ids)->delete();

        } else {
            
            $stock->quantity_returned = $stock->quantity_returned+$request->quantity_returned;
            $stock->save();
        }
        
        
        return response()->json(compact('stock'));
    }

    public function serials(Request $request){
        $stock_serials= StockSerialNo::where('stock_id', $request->stock_id)->get();
        return response()->json(compact('stock_serials'));
    }
}
