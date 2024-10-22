<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Creditor;
use App\CreditorPayment;

class CreditorController extends Controller
{
    public function index(Request $request){
    $creditors = Creditor::search($request->search)
    ->filter1($request->get('fromdate'))
    ->filter2($request->get('todate'))
    ->product($request->product)
    ->latest()
    ->paginate($request->rows, ['*'], 'page', $request->page);

    $creditors_accumulation=Creditor::search($request->search)
    ->filter1($request->get('fromdate'))
    ->filter2($request->get('todate'))
    ->product($request->product)
    ->latest()->get();
    $total_creditors=0;
    $total_balance=0;
    foreach($creditors_accumulation as $sale){
        
        $amount=$sale['amount'];
        $balance=$sale['balance'];
         $total_creditors+=$amount;
         $total_balance+=$balance;
    }
    return response()->json(compact('creditors','total_creditors','total_balance'));

    }

    public function show($creditor){
        $creditor = Creditor::where('id', $creditor)
        ->first();
        $payments= CreditorPayment::where('creditor_id', $creditor->id)->get();
        return response()->json(compact('creditor', 'payments'),200);
    }
}