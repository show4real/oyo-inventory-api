<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\PurchaseOrder;
use Validator;
use Str;
use App\Attribute;
use App\Branch;
use App\Product;
use App\Supplier;
use Carbon\Carbon;
use DB;
use App\Pos;
use App\Stock;
use App\StockSerialNo;
use App\PurchaseOrderSerial;
use App\Creditor;
use App\CreditorPayment;

class PurchaseOrderController extends Controller
{
    protected $purchase_order;
    
    public function __construct(PurchaseOrder $purchase_order, Attribute $attributes, 
            PurchaseOrderSerial $purchase_order_serials){
        $this->purchase_order = $purchase_order;
        $this->attributes = $attributes;
        $this->purchase_order_serials = $purchase_order_serials;
    }
    


    public function index($product_id,Request $request)
    {
        $purchase_orders=$this->purchase_order
        ->where('product_id',$product_id)
        ->latest()
        ->paginate($request->rows, ['*'], 'page', $request->page);
        $attributes = [];
        if($request->product_id)
            $attributes=Attribute::where('product_id',$request->product_id)
            ->with('attributevalues')->get();

        return response()->json(compact('purchase_orders','attributes'));
       
    }
    public function purchaseOrders(Request $request)
    {
        $purchase_orders=$this->purchase_order
       
        ->filter1($request->get('fromdate'))
        ->filter2($request->get('todate'))
        ->search($request->search)
        ->with('supplier')
        ->with('serials')
        ->order($request->order)
        ->latest()
        ->paginate($request->rows, ['*'], 'page', $request->page);

        $total_purchase=$this->purchase_order::getSales($request);
       
        $suppliers=Supplier::select('id','name')->get();
        $products=Product::select('id','name')->get();

        return response()->json(compact('purchase_orders','total_purchase','products','suppliers'));
       
    }

    public function stocks(Request $request)
    {
        $stocks=$this->purchase_order
        
        ->search($request->search)
        ->order($request->order)
        ->branch($request->branch)
        ->where('confirmed_at','!=',null)
        ->latest()
        ->paginate($request->rows, ['*'], 'page', $request->page);
        $suppliers=Supplier::select('id','name')->get();
        $branches=Branch::select('id','name')->get();
        $products=Product::select('id','name')->get();

        $total_stock=PurchaseOrder::where('confirmed_at','!=',null)
        ->select(DB::raw('sum(stock_quantity * unit_price) as total'))->get();

        return response()->json(compact('stocks','products','total_stock','suppliers','branches'));
       
    }

    public function filterAttributes(Request $request){
        $attributes = [];
        if($request->product_id){
            $attributes=$this->attributes->getAttributes($request);
        }
        return response()->json(compact('attributes'));
    }

    


    public function show(Request $request, $purchase_order){
        $purchase_order = $this->purchase_order->where('id', $purchase_order)->with('product')->firstOrFail();
        
        $attributes= $this->attributes->getAttributes($request);
        $purchase_order_serials= $this->purchase_order_serials->getPurchaseorderSerials($request);
        
        $suppliers=Supplier::select('id','name')->get();
        $branches=Branch::select('id','name')->get();
        
        return response()->json(compact('purchase_order','purchase_order_serials','attributes','suppliers','branches'));
        
    }

    
    public function save(Request $request)
    {
            $purchase_order=$this->purchase_order;
            $purchase_order->product_attributes = $request->product_attributes;
            $purchase_order->product_attributes_keys = $request->product_attributes_keys;
            $purchase_order->product_id = $request->product_id;
            $purchase_order->unit_price = $request->unit_price;
            $purchase_order->barcode =$request->barcode;
            $purchase_order->supplier_id = $request->supplier;
            $purchase_order->warehouse_id = $request->warehouse_id;
            $purchase_order->stock_quantity = $request->stock_quantity;
            $purchase_order->tracking_id = "TRK-" . strtoupper(Str::random(5));
            $purchase_order->save();
            return response()->json(compact('purchase_order'),200);
       
    }
    public function update($id, Request $request)
    {
       
        $purchase_order = $this->purchase_order->findOrFail($id);
        $purchase_order->product_attributes = $request->product_attributes;
        $purchase_order->product_attributes_keys = $request->product_attributes_keys;
        $purchase_order->product_id = $request->product_id;
        $purchase_order->unit_price = $request->unit_price;
        $purchase_order->barcode =$request->barcode;
        $purchase_order->supplier_id = $request->supplier;
        $purchase_order->warehouse_id = $request->warehouse_id;
        $purchase_order->stock_quantity = $request->stock_quantity;
        $purchase_order->save();
        return response()->json(compact('purchase_order'));
    }


    public function confirmOrder($id, Request $request)
    {
       
        $purchase_order = $this->purchase_order->findOrFail($id);
        
        if($request->received_at == ''){
            $purchase_order->rejected_at = now();
            $purchase_order->confirmed_at = null;
            $purchase_order->received_at = null;
            $purchase_order->quantity_returned = 0;
            $purchase_order->quantity_moved=0;
            $purchase_order->save();
        }else{
            $purchase_order->received_at = Carbon::parse($request->received_at);
            $purchase_order->rejected_at = null;
            $purchase_order->unit_selling_price = str_replace(',', '', $request->selling_price);
            $purchase_order->confirmed_at = now();
            $save = $purchase_order->save();
            if($save){
            
                for($v=1; $v<= $purchase_order->stock_quantity; $v++){
                    $purchase_order_serial= new PurchaseOrderSerial();
                    $purchase_order_serial->purchase_order_id = $purchase_order->id;
                    $purchase_order_serial->save();
                }
                
              
                $amount =  $purchase_order->unit_price*$purchase_order->stock_quantity;
                $creditor= new Creditor();
                $creditor->product_id = $purchase_order->product_id;
                $creditor->purchase_order_id = $purchase_order->id;
                $creditor->amount = $amount;
                $creditor->supplier_id = $purchase_order->supplier_id;
                $creditor->save();

                $payment = new CreditorPayment();
                $payment->amount = $amount;
                $payment->amount_paid = 0;
                $payment->creditor_id = $creditor->id;
                $payment ->balance = $amount;
                $payment->payment_type = "CREDITOR";
                $payment->save();
            }
        }
        return response()->json(compact('purchase_order'));
    }

    public function returnOrder($id, Request $request)
    {
       
        $purchase_order = $this->purchase_order->findOrFail($request->id);
        
        if($request->quantity_returned){
            $qty=$purchase_order->quantity_returned+$request->quantity_returned;
            $purchase_order->quantity_returned = $qty;
            $purchase_order->save();
        }
        if($request->purchase_order_serials){
            foreach($request->purchase_order_serials as $serial_ids){
                $purchase_order_serials[]=$serial_ids['value'];
            }
            $purchase_serial= PurchaseOrderSerial::whereIn('id', $purchase_order_serials)->update(['returned_at' => now()]);

        }
            return response()->json(compact('purchase_order'), 200);
    }


     public function editPrice(Request $request)
    {
       
        $purchase_order = $this->purchase_order->findOrFail($request->id);
        $purchase_order->unit_selling_price = $request->unit_selling_price;
        $purchase_order->save();
        
        return response()->json(compact('purchase_order'), 200);
    }

    


    public function moveOrder($id, Request $request)
    {
       
        $purchase_order = $this->purchase_order->findOrFail($id);
        $purchase_order->quantity_moved =$purchase_order->quantity_moved+$request->quantity_moved;
        $purchase_order->save();

        $stock= Stock::where('purchase_order_id', $id)->where('branch_id', $request->branch_id)->first();
        $prev_quantity= $stock !== null ? $stock->stock_quantity : 0;

        $quantity_moved = $request->quantity_moved+$prev_quantity;
        $new_stock = Stock::updateOrCreate(

            ['purchase_order_id' => $id,'branch_id' => request('branch_id')],
            ['stock_quantity' => $quantity_moved, 'product_id' =>$purchase_order->product_id, 
            'supplier_id' => $purchase_order->supplier_id]
        
        );

        if($request->values){
            
            $v=$request->values;
            foreach($v as $index=>$values) {

                PurchaseOrderSerial::updateOrCreate(

                    ['purchase_order_id' => request('id'),'serial_no' =>  $v[$index]['serial_no']],
                    ['moved_at' => now(), 'branch_moved_to' => request('branch_id')]
                
                );

                $stock_serial = new StockSerialNo();
                $stock_serial->serial_no =  $v[$index]['serial_no'];
                $stock_serial->stock_id = $new_stock->id;
                $stock_serial->save();
                
            }
        }
           
        
        return response()->json(compact('purchase_order'));
    }

    
    public function editSerial(Request $request){
        $purchase_order_serial = PurchaseOrderSerial::where('id', $request->id)->first();
        $check_serial = PurchaseOrderSerial::where('purchase_order_id', $purchase_order_serial->purchase_order_id)
        ->where('serial_no', $request->serial_no)->exists();
        if($check_serial)
        return response()->json("Serial No existed", 422);
        $purchase_order_serial->serial_no = $request->serial_no;
        $purchase_order_serial->save();
        return response()->json(compact('purchase_order_serial'));
    }
    
}
