<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Product;
use App\Pos;
use App\Supplier;
use App\Branch;
use App\PurchaseOrder;
use App\Invoice;
use App\CreditorPayment;

class DashboardController extends Controller
{
   public function index(Request $request){
    $users= User::where('status', 1)->get();
    $product_count= Product::count();
    $branch_count= Branch::count();
    $supplier_count= Supplier::count();
    $sales= Pos::latest()
    ->get();
    $purchases= PurchaseOrder::
        where('confirmed_at','!=', null)
        ->latest()
        ->get();
    $total_purchases=0;
    foreach($purchases as $index=>$values) {
        $total_purchases+=$purchases[$index]['stock_quantity'] * $purchases[$index]['unit_price'];
    }
    $total_sales = Invoice::sum('amount');
    $total_balance = Invoice::sum('balance');
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
    return response()->json(compact('users','total_purchases','total_expenses','total_sales','total_balance','product_count','branch_count','supplier_count'));

   }

   
    
}
