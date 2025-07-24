<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Payment;
use App\Invoice;

class PaymentController extends Controller
{
    public function index(Request $request){
        $payments= Payment::where('organization_id', auth()->user()->organization_id)
        ->latest()
        ->client($request->client)
        ->start($request->startdate)
        ->end($request->enddate)
        ->paginate($request->rows, ['*'], 'page', $request->page);
        return response()->json(compact('payments'),200);
    }
    public function save(Request $request){

        $new_balance=($request->total_amount-($request->amount_paid + $request->previous_payment));
       

        $invoice=Invoice::where('organization_id', auth()->user()->organization_id)->where('id', $request->invoice_id)->first();

        $payment= new Payment();
        $payment->amount_paid= $request->amount_paid;
        $payment->amount=$request->total_amount;
        $payment->balance = $new_balance;
        $payment->invoice_id = $request->invoice_id;
        $payment->client_id = $invoice->client_id;
        $payment->organization_id = auth()->user()->organization_id
        $payment->save();


        $total_amount_paid = Payment::where('organization_id', auth()->user()->organization_id)->where('invoice_id', $request->invoice_id)->sum('amount_paid');
        
        $invoice->amount_paid =$total_amount_paid;
        $invoice->balance=$total_amount_paid - $invoice->amount;
        $invoice->organization_id = auth()->user()->organization_id;
        $invoice->save();

        
        return response()->json(compact('payment','invoice'));

    }

    public function update(Request $request){

        $payment= Payment::where('organization_id', auth()->user()->organization_id)->where('id', $request->id)->first();

        $payment_before_update= $payment->amount_paid;
        $new_payment= $request->amount_paid;

        $new_balance=($request->total_amount-(($request->amount_paid + $request->previous_payment)-$payment_before_update));

        $payment->amount_paid= $request->amount_paid;
        $payment->amount=$request->total_amount;
        $payment->balance = $new_balance;
        $payment->save();

       

        $total_amount_paid = Payment::where('organization_id', auth()->user()->organization_id)->where('invoice_id', $request->invoice_id)->sum('amount_paid');

        $invoice=Invoice::where('id', $request->invoice_id)->first();
        $invoice->amount_paid = $total_amount_paid;
        $invoice->balance=$new_balance;
        $invoice->save();

        
        return response()->json(compact('payment','invoice'));
    }

}
