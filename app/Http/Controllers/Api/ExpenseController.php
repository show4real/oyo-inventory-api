<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CreditorPayment;
use App\User;

class ExpenseController extends Controller
{
    public function index(Request $request){
        $expenses= CreditorPayment::
        where('amount_paid','>', 0)
        ->start($request->fromdate)
        ->end($request->todate)
        ->latest()
        ->paginate($request->rows, ['*'], 'page', $request->page);

        $sum_expenses=CreditorPayment::
        where('amount_paid','>', 0)
        ->start($request->fromdate)
        ->end($request->todate)
        ->latest()->get();
        $total_expenses=0;
        foreach($sum_expenses as $expense){
            $sum_expenses=$expense['amount_paid'];
             $total_expenses+=$sum_expenses;
        }
        return response()->json(compact('expenses','total_expenses'),200);
    }
    public function save(Request $request){

        $user = User::where('id', auth()->user()->id)->first();
        $payment= new CreditorPayment();
        $payment->amount_paid= $request->amount_paid;
        $payment->branch_id = $user->branch_id;
        $payment->created_by = $user->id;
        $payment->updated_by = $user->id;
        $payment->amount= $request->amount_paid;
        $payment->receiver= $request->receiver;
        $payment->payment_type = "EXPENSE";
        $payment->payment_mode = $request->payment_mode;
        $payment->description = $request->description;
        $payment->balance =0;
        $payment->save();
        
        return response()->json(compact('payment'));

    }

    public function update(Request $request){

        $payment= CreditorPayment::where('id', $request->id)->first();
        $user = User::where('id', auth()->user()->id)->first();
        $payment->amount_paid= $request->amount_paid;
        $payment->amount= $request->amount_paid;
        $payment->receiver= $request->receiver;
        $payment->payment_type = "EXPENSE";
        $payment->payment_mode = $request->payment_mode;
        $payment->description = $request->description;
        $payment->balance =0;
        $payment->updated_by = $user->id;
        $payment->save();
    
        
        return response()->json(compact('payment'));
    }

}
