<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CreditorPayment;
use App\User;

class CreditorPaymentController extends Controller
{
    public function index(Request $request){
        $payments= CreditorPayment::latest()
        ->start($request->startdate)
        ->end($request->enddate)
        ->paginate($request->rows, ['*'], 'page', $request->page);
        return response()->json(compact('payments'),200);
    }
    public function save(Request $request){
        $user = User::where('id', auth()->user()->id)->first();
        $new_balance=($request->total_amount-($request->amount_paid + $request->previous_payment));
        $total=$request->amount_paid + $request->previous_payment;
        $payment= new CreditorPayment();
        $payment->amount_paid= $request->amount_paid;
        $payment->payment_type = "CREDITOR";
        $payment->payment_mode = $request->payment_mode;
        $payment->description = $request->description;
        $payment->amount=$request->total_amount;
        $payment->balance = $new_balance;
        $payment->creditor_id = $request->creditor_id;
        $payment->branch_id = $user->branch_id;
        $payment->created_by = $user->id;
        $payment->updated_by = $user->id;
        $payment->save();
        
        return response()->json(compact('payment'));

    }

    public function update(Request $request){

        $payment= CreditorPayment::where('id', $request->id)->first();
        $user = User::where('id', auth()->user()->id)->first();
        $payment_before_update= $payment->amount_paid;
        $new_payment= $request->amount_paid;
        $new_balance=($request->total_amount-(($request->amount_paid + $request->previous_payment)-$payment_before_update));
        $payment->amount_paid= $request->amount_paid;
        $payment->amount=$request->total_amount;
        $payment->balance = $new_balance;
        $payment->payment_type = "CREDITOR";
        $payment->payment_mode = $request->payment_mode;
        $payment->description = $request->description;
        $payment->updated_by = $user->id;
        $payment->save();
    
        
        return response()->json(compact('payment'));
    }

}
