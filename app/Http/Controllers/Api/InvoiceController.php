<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Client;
use App\InvoiceItem;
use App\Invoice;
use App\Payment;
use Validator;
use App\CompanySettings;
use App\Pos;
use App\User;

class InvoiceController extends Controller
{
    public function index(Request $request){
        $invoices = Invoice::
        search($request->search)
        ->filter1($request->get('fromdate'))
        ->currency($request->currency)
        ->filter2($request->get('todate'))
        ->order($request->order)
        ->cashier($request->cashier_id)
        ->latest()
        ->paginate($request->rows, ['*'], 'page', $request->page);
        $company= CompanySettings::first();
        $sales=Invoice::search($request->search)
        ->filter1($request->get('fromdate'))
        ->filter2($request->get('todate'))
        ->order($request->order)
        ->currency($request->currency)
        ->cashier($request->cashier_id)->get();
        $total_sales=0;
        $total_balance=0;
        $total_discount=0;
        foreach($sales as $sale){
            
            $sales=$sale['amount'];
            $balance=$sale['balance'];
            $discount = $sale['discount'];
             $total_sales+=$sales;
             $total_balance+=$balance;
             $total_discount+=$discount;
        }
        $cashiers = User::get();
        return response()->json(compact('invoices','company','total_sales','total_balance','total_discount','cashiers'));
    }

    public function index2(Request $request){
        $cashier=auth()->user()->id;
        $invoices = Invoice::
        where('cashier_id', $cashier)
        ->search($request->search)
        ->currency($request->currency)
        ->filter1($request->get('fromdate'))
        ->filter2($request->get('todate'))
        ->order($request->order)
        ->latest()
        ->paginate($request->rows, ['*'], 'page', $request->page);
        $company= CompanySettings::first();

        $sales=Invoice::where('cashier_id', $cashier)->search($request->search)
        ->filter1($request->get('fromdate'))
        ->filter2($request->get('todate'))
        ->order($request->order)
        ->currency($request->currency)
        ->cashier($request->cashier_id)->get();
        $total_sales=0;
        $total_balance=0;
        $total_discount=0;
        foreach($sales as $sale){
            
            $sales=$sale['amount'];
            $balance=$sale['balance'];
            $discount = $sale['discount'];
             $total_sales+=$sales;
             $total_balance+=$balance;
             $total_discount+=$discount;
        }
        return response()->json(compact('invoices','company','total_sales','total_balance','total_discount'));
    }

    public function lastInvoice(){
        $invoice = Invoice::
        latest()
        ->first();
        return response()->json(compact('invoice'),200);
    }



    public function show(Invoice $invoice){
        $invoice = Invoice::where('id', $invoice->id)
        ->with('payments')
        ->with('client')
        ->first();
        $items= InvoiceItem::where('invoice_id', $invoice->id)->get();
        $pos_items = Pos::where('invoice_id', $invoice->id)->with('stock')->with('order')->get();
        $payments= Payment::where('invoice_id', $invoice->id)->get();
        return response()->json(compact('invoice','items','payments','pos_items'),200);
    }

    public function show2(Invoice $invoice){
        $cashier=auth()->user()->id;
        $invoice = Invoice::where('id', $invoice->id)
        ->where('cashier_id', $cashier)
        ->with('payments')
        ->with('client')
        ->first();
        $items= InvoiceItem::where('invoice_id', $invoice->id)->get();
        $pos_items = Pos::where('invoice_id', $invoice->id)->with('stock')->with('order')->get();
        $payments= Payment::where('invoice_id', $invoice->id)->get();
        return response()->json(compact('invoice','items','payments','pos_items'),200);
    }

    public function save(Request $request){
        $validator = Validator::make($request->all(), [
            'invoice_no' => 'required|unique:invoices',
        ]);

      if($validator->fails()){
          return response()->json($validator->messages(), 422);
        }
        $balance = $request->total_amount + $request->amount_paid;
        $invoice= new Invoice();
        $invoice->invoice_no=$request->invoice_no;
        $invoice->cashier_id=auth()->user()->id;
        $invoice->purchase_order_no =$request->purchase_order_no;
        $invoice->description =$request->description;
        $invoice->client_id = $request->client_id;
        $invoice->currency = $request->currency;
        $invoice->issued_date = $request->issue_date;
        $invoice->due_date = $request->due_date;
        $invoice->amount = $request->total_amount;
        $invoice->amount_paid = $request->amount_paid;
        $invoice->balance = $balance;
        $invoice->payment_type = "MANUAL";
        $invoice->invoice_type = $balance > 0 ? 'Debit' : 'Credit';
        $save= $invoice->save();
        if($save){
           
            for ($i = 0; $i < count($request->item_description); $i++) {
                $invoice_item= new InvoiceItem();
                $invoice_item->invoice_id = $invoice->id;
                $invoice_item->description = $request->item_description[$i];
                $invoice_item->quantity = $request->quantity[$i];
                $invoice_item->rate = $request->rate[$i];
                $invoice_item->amount = $request->amount[$i];
                $invoice_item->save(); 
                $items[]=$invoice_item;
            }
            $payment= new Payment();
            $payment->amount_paid= $request->amount_paid;
            $payment->amount=$request->total_amount;
            $payment->balance = $balance;
            $payment->invoice_id = $invoice->id;
            $payment->save();   
            $client= Client::where('id',$request->client_id)->first();
            $invoice = Invoice::with('client')->with('payments')->latest()->first();
              
        }
        return response()->json(compact('invoice','payment','items','client'));
    }

    public function update(Request $request, Invoice $invoice){

        $invoice->purchase_order_no =$request->purchase_order_no;
        $invoice->description =$request->invoice_description;
        $invoice->client_id = $request->client_id;
        $invoice->cashier_id = auth()->user()->id;
        $invoice->currency = $request->currency;
        $invoice->issued_date = $request->issued_date;
        $invoice->due_date = $request->due_date;
        $invoice->amount = $request->total_amount;
        $invoice->amount_paid = $request->amount_paid;
        $invoice->balance = $request->balance;
        $save= $invoice->save();
        $delete_items= InvoiceItem::where('invoice_id', $invoice->id)->delete();
        if($save && $delete_items){
            for ($i = 0; $i < count($request->description); $i++) {

                InvoiceItem::updateOrCreate([
                    'invoice_id' => $invoice->id,
                    'description' => $request->description[$i],
                ],
                ['description' => $request->description[$i],
                'quantity' => $request->quantity[$i],
                'rate' => $request->rate[$i],
                'amount' => $request->amount[$i],]);
               
               
            }
            $payment =Payment::where('invoice_id', '=', $invoice->id)
            ->update([
                'amount_paid' => $request->amount_paid,
                'amount' =>  $request->total_amount, 
                'balance' =>  $request->balance
            ]);
        
              
        }
        return response()->json(compact('invoice','payment'));
    }
   
    public function delete($id, Request $request){
        $invoice = Invoice::findOrFail($id);
        $invoice->remove();
        return response()->json(true);
    }
}
