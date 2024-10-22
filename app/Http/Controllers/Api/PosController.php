<?php

namespace App\Http\Controllers\Api;
use App\PurchaseOrder;
use App\Pos;
use App\Product;
use Valiadtor;
use Str;
use App\Stock;
use App\User;
use App\StockSerialNo;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CompanySettings;
use App\Invoice;
use App\Payment;

class PosController extends Controller
{

    protected $pos;
    
    public function __construct(Pos $pos)
    {
        $this->pos = $pos;
    }

    public function multPosOrder(Request $request){
        $currency = CompanySettings::first()->currency;
        $sale_orders=[];
        $pos_order=[];
        $payment_mode=$request->payment_mode;
        $payment_type = "POS";
        $qty=0;
        $transact_id="TRANSAC-".strtoupper(Str::random(15));
        $sell_by_serial_no= CompanySettings::first()->sell_by_serial_no;
        if($request->cart_items){
            $v=$request->cart_items;
            $total_purchase = 0;
            foreach($v as $index=>$cart_items) {

                
                $sale_orders =Stock::firstOrNew(['id' => $v[$index]['id']]);
                $sale_orders->quantity_sold = ($sale_orders->quantity_sold + $v[$index]['quantity']);
                $sale_orders->save();
                $sold_at=now();
                if($sell_by_serial_no == 1){
                    $serials = StockSerialNo::whereIn('id', $v[$index]['new_serials'])->update(['sold_at' => $sold_at]);
                }
                $total_purchase +=  $v[$index]['quantity'] *  $v[$index]['order']['unit_selling_price'];
               

                // save for pos record
                $pos_order =Pos::create(['purchase_order_id' => $v[$index]['purchase_order_id']]);
                $pos_order->transaction_id = $transact_id;
                $client_id = $request->client_id;
                $pos_order->qty_sold =$v[$index]['quantity'];
                $pos_order->supplier_id =$v[$index]['supplier_id'];
                if($sell_by_serial_no == 1){
                    $pos_order->serials =$v[$index]['new_serials'];
                }
                $pos_order->stock_id =$v[$index]['id'];
                $pos_order->product_id =$v[$index]['product_id'];
                $pos_order->cashier_id = auth()->user()->id;

                $pos_order->payment_mode=$payment_mode;
                $pos_order->channel='pos_order';
                $pos_order->save();
                $sold_at=now();
            }
            // Save Invoice
            $now = Carbon::now();
            $invoice= new Invoice();
            $invoice->invoice_no=$request->invoice_no;
            $invoice->transaction_id = $transact_id;
            $invoice->cashier_id=auth()->user()->id;
            $invoice->description = "Sales from POS Menu";
            $invoice->payment_type = "POS";
            $invoice->client_id = $request->client_id;
            $invoice->currency = $currency;
            $invoice->issued_date = $now;
            $invoice->due_date = $request->due_date;
            $invoice->amount = $total_purchase;
            $invoice->amount_paid = $request->amount_paid;
            $invoice->balance = $total_purchase - $request->amount_paid;
            $invoice->save();
            $payment= new Payment();
            $payment->amount_paid= $request->amount_paid;
            $payment->amount=$total_purchase;
            $payment->balance = $total_purchase - $request->amount_paid;
            $payment->invoice_id = $invoice->id;
            $payment->save(); 
            $update_pos =Pos::where('transaction_id',$transact_id)->update(['invoice_id' => $invoice->id]);

            //$pos_sales=Pos::where('transaction_id',$transact_id)->with('stock')->with('order')->get();
            $invoice = Invoice::where('id', $invoice->id)
            ->with('payments')
            ->with('client')
            ->first();
            $pos_items = Pos::where('invoice_id', $invoice->id)->with('stock')->with('order')->get();

            return response()->json(compact('pos_order','sold_at','payment_mode','invoice','pos_items'));
        }
       
    }

    public function products(Request $request){
        $products = Product::select('id','name')->get();
        return response()->json(compact('products'));
    }

    public function getPosSales(Request $request)
    {
        
        $users= User::select('id','firstname','lastname')->get();
        $pos_sales=$this->pos
        ->search($request->search)
        ->order($request->order)
        ->employee($request->user)
        ->product($request->product)
        ->startdate($request->fromdate)
        ->enddate($request->todate)
        ->with('stock')->with('order')
        ->latest()
        ->paginate($request->rows, ['*'], 'page', $request->page);
        
        $sales=$this->pos->search($request->search)
        ->order($request->order)
        ->employee($request->user)
        ->product($request->product)
        ->startdate($request->fromdate)
        ->enddate($request->todate)->get();
        $total_sales=0;
        foreach($sales as $sale){
            
            $result=$sale['selling_price'] * $sale['qty_sold'];
             $total_sales+=$result;
        }

     


       
   
      
        return response()->json(compact('pos_sales','users','total_sales'));
    }

    public function getPosSales2(Request $request)
    {
        $user=auth()->user()->id;
        $users= User::select('id','firstname','lastname')->get();
       

        $pos_sales=$this->pos
        ->where('cashier_id', $user)
        ->search($request->search)
        ->order($request->order)
        ->employee($request->user)
        ->startdate($request->fromdate)
        ->enddate($request->todate)
        ->with('stock')->with('order')
        ->latest()
        ->paginate($request->rows, ['*'], 'page', $request->page);
        
        
        return response()->json(compact('pos_sales','users'));
    }

    public function getTransactionDetails(Request $request){
        $transaction_detail=$this->pos
        ->where('transaction_id', $request->transaction_id)
        ->with('stock')->with('order')->get();
        $invoice = Invoice::where('transaction_id', $request->transaction_id)->first();
        return response()->json(compact('transaction_detail','invoice'));
    }

    public function getTransactionDetails2(Request $request){
        $transaction_detail=$this->pos
        ->where('cashier_id', auth()->user()->id)
        ->where('transaction_id', $request->transaction_id)
        ->with('stock')->with('order')->get();
        return response()->json(compact('transaction_detail'));
    }

    
}
