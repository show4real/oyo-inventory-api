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
use DB;

class PosController extends Controller
{

    protected $pos;
    
    public function __construct(Pos $pos)
    {
        $this->pos = $pos;
    }

    public function multPosOrder(Request $request){
        $currency = CompanySettings::where('organization_id', auth()->user()->organization_id)->first()->currency;
        $sale_orders=[];
        $pos_order=[];
        $payment_mode = $request->payment_mode;
        
        $transact_id="TRANSAC-".strtoupper(Str::random(15));
        $sell_by_serial_no= CompanySettings::where('organization_id', auth()->user()->organization_id)->first()->sell_by_serial_no;
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
                $pos_order->unit_selling_price = $v[$index]['order']['unit_selling_price'];
                $pos_order->supplier_id =$v[$index]['supplier_id'];
                if($sell_by_serial_no == 1){
                    $pos_order->serials =$v[$index]['new_serials'];
                }
                $pos_order->stock_id =$v[$index]['id'];
                $pos_order->product_id =$v[$index]['product_id'];
                $pos_order->cashier_id = auth()->user()->id;

                $pos_order->payment_mode=$payment_mode;
                $pos_order->channel='pos_order';
                $pos_order->organization_id = auth()->user()->organization_id;
                $pos_order->save();
                $sold_at=now();
            }
            // Save Invoice
            $now = Carbon::now();
            $invoice= new Invoice();
            $invoice->invoice_no=$request->invoice_no;
            $invoice->transaction_id = $transact_id;
            $invoice->cashier_id=auth()->user()->id;
            $invoice->organization_id = auth()->user()->organization_id;
            $invoice->description = "Sales from POS Menu";
            $invoice->payment_type = "POS";
            $invoice->client_id = $request->client_id;
            $invoice->currency = $currency;
            $invoice->issued_date = $now;
            $invoice->due_date = $request->due_date;
            $invoice->amount = $total_purchase;
            $invoice->amount_paid = $request->amount_paid;
            $invoice->balance = $total_purchase - $request->amount_paid;
            $invoice->payment_mode = $payment_mode;
            $invoice->save();
            
            $payment= new Payment();
            $payment->amount_paid= $request->amount_paid;
            $payment->amount=$total_purchase;
            $payment->balance = $total_purchase - $request->amount_paid;
            $payment->invoice_id = $invoice->id;
            $payment->client_id = $request->client_id;
            $payment->organization_id = auth()->user()->organization_id;
            $payment->save(); 
            $update_pos =Pos::where('organization_id', auth()->user()->organization_id)->where('transaction_id',$transact_id)->update(['invoice_id' => $invoice->id]);

            //$pos_sales=Pos::where('transaction_id',$transact_id)->with('stock')->with('order')->get();
            $invoice = Invoice::where('organization_id', auth()->user()->organization_id)->where('id', $invoice->id)
            ->with('payments')
            ->with('client')
            ->first();
            $pos_items = Pos::where('organization_id', auth()->user()->organization_id)->where('invoice_id', $invoice->id)->with('stock')->with('order')->get();

            $invoices = Invoice::where('organization_id', auth()->user()->organization_id)->where('client_id', $invoice->client_id)->get();

            
            $balance = $total_purchase - $request->amount_paid;

            $total_balance = $invoices->sum('client_balance');

            $prev_balance = $total_balance - $balance;
            

            return response()->json(compact('pos_order','sold_at','payment_mode','invoice','pos_items','total_balance','balance','prev_balance'));
        }
       
    }




    public function editMultPosOrder(Request $request)
    {
        
        $invoice = Invoice::where('organization_id', auth()->user()->organization_id)->with(['payments', 'client'])->findOrFail($request->invoice_id);

        
        $posRecords = Pos::where('invoice_id', $invoice->id)->get();
        if ($posRecords->isNotEmpty()) {
            foreach ($posRecords as $pos) {
                $pos->stock()->decrement('quantity_sold', $pos->qty_sold);
                $pos->delete();
            }
        }

        $sale_orders = [];
        $pos_order=[];
        $payment_mode = $request->payment_mode;
        $transact_id = "TRANSAC-" . strtoupper(Str::random(15));
        $total_purchase = 0;

        if ($request->cart_items) {
            foreach ($request->cart_items as $cart_item) {
                $stock = Stock::firstOrNew(['id' => $cart_item['id']]);
                $stock->quantity_sold += $cart_item['quantity'];
                $stock->save();

                $total_purchase += $cart_item['quantity'] * $cart_item['order']['unit_selling_price'];

                // Create POS record for each item
                $pos_order = Pos::create([
                    'purchase_order_id' => $cart_item['purchase_order_id'],
                    'transaction_id' => $transact_id,
                    'qty_sold' => $cart_item['quantity'],
                    'unit_selling_price' => $cart_item['order']['unit_selling_price'],
                    'supplier_id' => $cart_item['supplier_id'],
                    'stock_id' => $cart_item['id'],
                    'product_id' => $cart_item['product_id'],
                    'edited_by' => auth()->user()->id,
                    'cashier_id' => auth()->user()->id,
                    'payment_mode' => $payment_mode,
                    'channel' => 'pos_order',
                    'organization_id' => auth()->user()->organization_id
                ]);
            }

    
            $invoice->update([
                'transaction_id' => $transact_id,
                'edited_by' => auth()->user()->id,
                'description' => "Sales from POS Menu",
                'payment_type' => "POS",
                'client_id' => $request->client_id,
                'issued_date' => now(),
                'amount' => $total_purchase,
                'amount_paid' => $request->amount_paid,
                'balance' => $total_purchase - $request->amount_paid,
                'payment_mode' => $payment_mode,
                'organization_id' => auth()->user()->organization_id
            ]);

            
            $payment = Payment::where('organization_id', auth()->user()->organization_id)->where('invoice_id', $request->invoice_id)->first();
            $payment->update([
                'amount_paid' => $request->amount_paid,
                'amount' => $total_purchase,
                'balance' => $total_purchase - $request->amount_paid,
                'client_id' => $request->client_id,
                'organization_id' => auth()->user()->organization_id
            ]);

            
            Pos::where('transaction_id', $transact_id)->update(['invoice_id' => $invoice->id]);

            
            $pos_items = Pos::where('organization_id', auth()->user()->organization_id)->where('invoice_id', $invoice->id)->with(['stock', 'order'])->get();

           
            $invoices = Invoice::where('organization_id', auth()->user()->organization_id)->where('client_id', $invoice->client_id)->get();

            $total_balance = $invoices->sum('client_balance');

            $balance = $total_purchase - $request->amount_paid;

            $prev_balance = $total_balance - $balance;

            $sold_at = now();

            
            return response()->json(compact('pos_order','sold_at', 'payment_mode', 'invoice', 'pos_items', 'total_balance','prev_balance','balance'));
        }
    }


    public function products(Request $request){
        $products = Product::where('organization_id', auth()->user()->organization_id)->select('id','name')->get();
        return response()->json(compact('products'));
    }

   public function getPosSales(Request $request) 
    {
        $users = User::where('organization_id', auth()->user()->organization_id)
            ->select('id', 'firstname', 'lastname')
            ->get();
        
        $pos_sales = $this->pos
            ->where('organization_id', auth()->user()->organization_id)
            ->search($request->search)
            ->order($request->order)
            ->employee($request->user)
            ->product($request->product)
            ->startdate($request->fromdate)
            ->enddate($request->todate)
            ->with('stock')
            ->with('order')
            ->latest()
            ->paginate($request->rows, ['*'], 'page', $request->page);
        
        $sales = $this->pos
            ->where('organization_id', auth()->user()->organization_id)
            ->search($request->search)
            ->order($request->order)
            ->employee($request->user)
            ->product($request->product)
            ->startdate($request->fromdate)
            ->enddate($request->todate)
            ->get();
        
        $total_sales = 0;
        $total_sold = 0;
        $total_instock = 0;
        $total = 0;
        
        foreach($sales as $sale) {
            $result = $sale['selling_price'] * $sale['qty_sold'];
            $total_sales += $result;
            $total_sold += $sale['qty_sold'];
        }
        
        // Calculate totals when product is specified
        if ($request->product) {
            // Get stock information for the specific product
            $stock_query = $this->pos
                ->where('organization_id', auth()->user()->organization_id)
                ->product($request->product);
            
            // Apply date filters if specified
            if ($request->fromdate) {
                $stock_query->startdate($request->fromdate);
            }
            if ($request->todate) {
                $stock_query->enddate($request->todate);
            }
            
            $stock_data = $stock_query->with('stock')->get();
            
            // Calculate total instock from related stock records
            foreach($stock_data as $item) {
                if ($item->stock) {
                    $total_instock += $item->stock->stock_quantity ?? 0;
                }
            }
            
            // Calculate combined total (instock + sold)
            $total = $total_instock + $total_sold;
        }
        
        return response()->json(compact(
            'pos_sales', 
            'users', 
            'total_sales', 
            'total', 
            'total_sold', 
            'total_instock'
        ));
    }

    public function getPosTransactions(Request $request)
    {
        
        $users= User::where('organization_id', auth()->user()->organization_id)->select('id','firstname','lastname')->get();
         $latestIds = DB::table(DB::raw('(
            SELECT MAX(id) as max_id 
            FROM pos 
            WHERE organization_id = ?
            GROUP BY transaction_id
        ) as subquery'))
            ->setBindings([auth()->user()->organization_id])
            ->pluck('max_id');

   

        // Then get the actual records with those IDs
        $pos_sales = $this->pos
            ->where('organization_id', auth()->user()->organization_id)
            ->whereIn('id', $latestIds)
            ->search($request->search)
            ->order($request->order)
            ->employee($request->user)
            ->product($request->product)
            ->startdate($request->fromdate)
            ->enddate($request->todate)
            ->with('stock')
            ->with('order')
            ->latest()
            ->paginate($request->rows, ['*'], 'page', $request->page);
        
        $sales=$this->pos
        ->where('organization_id', auth()->user()->organization_id)
        ->search($request->search)
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
        $users= User::where('organization_id', auth()->user()->organization_id)->select('id','firstname','lastname')->get();
       

        $pos_sales=$this->pos
        ->where('organization_id', auth()->user()->organization_id)
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
        ->where('organization_id', auth()->user()->organization_id)
        ->where('transaction_id', $request->transaction_id)
        ->with('stock')->with('order')->get();
        $invoice = Invoice::where('transaction_id', $request->transaction_id)->first();

        $clientInvoices = Invoice::where('client_id', $invoice->client_id)
                ->get();

        $total_balance = $clientInvoices->sum('client_balance');

        $balance = $invoice->amount - $invoice->amount_paid;

        $prev_balance = $total_balance - $balance;

        return response()->json(compact('transaction_detail','invoice','total_balance','balance','prev_balance'));
    }

    public function getTransactionDetails2(Request $request){
        $transaction_detail=$this->pos
        ->where('organization_id', auth()->user()->organization_id)
        ->where('cashier_id', auth()->user()->id)
        ->where('transaction_id', $request->transaction_id)
        ->with('stock')->with('order')->get();
        return response()->json(compact('transaction_detail'));
    }

    
}
