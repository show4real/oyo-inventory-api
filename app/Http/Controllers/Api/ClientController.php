<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;
use App\Client;
use App\Invoice;
use App\User;
use App\Payment;

class ClientController extends Controller
{
    public function index(Request $request){
        $clients = Client::where('organization_id', auth()->user()->organization_id)
        ->search($request->search)
        ->latest()
        ->paginate($request->rows, ['*'], 'page', $request->page);
        return response()->json(compact('clients'));
    }

    public function allClients(Request $request){
        $clients = Client::where('organization_id', auth()->user()->organization_id)
        ->search($request->search)
        ->latest()
        ->get();
        return response()->json(compact('clients'));
    }

    public function cashiers(Request $request){
        $cashiers = User::where('organization_id', auth()->user()->organization_id)
        ->search($request->search)
        ->latest()
        ->paginate($request->rows, ['*'], 'page', $request->page);
        return response()->json(compact('cashiers'));
    }

    public function show(Client $client){
        $client = client::where('id', $client->id)->first();
        return response()->json(compact('client'),200);
    }

    public function save(Request $request){
      
       
        $client= new Client();
        $client->name=$request->name;
        $client->email =$request->email;
        $client->phone = $request->phone;
        $client->address = $request->address;
        $client->organization_id = auth()->user()->organization_id;
        $client->save();
        return response()->json(compact('client'));
    }

    public function update(Request $request, Client $client){

        // $validator = Validator::make($request->all(), [
        //     'email' => 'unique:clients,email,'. $client->id
        // ]);

        // if($validator->fails()){
        //   return response()->json($validator->messages(), 422);
        // }
       
        $client->name=$request->name;
        $client->email =$request->email;
        $client->phone = $request->phone;
        $client->address = $request->address;
        $client->save();
        return response()->json(compact('client'));
    }
    public function search(Request $request){
        $clients = Client::where('organization_id', auth()->user()->organization_id)->search($request->search)->get();

        return response()->json(compact('clients'));
    }

    public function delete($id, Request $request){
        $client = Client::findOrFail($id);
        $client->delete();
        return response()->json(true);
    }

    public function clientPayments(Request $request){

        $client_invoices_payments = Invoice::where('organization_id', auth()->user()->organization_id)
            ->where('client_id', $request->client_id)
            ->with('payments')
            ->latest()
            ->paginate($request->rows, ['*'], 'page', $request->page);

        $clientInvoices = Invoice::where('organization_id', auth()->user()->organization_id)
                ->where('client_id', $request->client_id)
                ->get();

        $invoice = Invoice::where('organization_id', auth()->user()->organization_id)->where('client_id', $request->client_id)->latest()->first();

        $payment = Payment::where('organization_id', auth()->user()->organization_id)->where('client_id', $request->client_id)->latest()->first();

        if($invoice){
             
            $total_balance = $clientInvoices->sum('client_balance');
        
            $last_paid = $payment->amount_paid ?? 0;

            $total_paid = Invoice::where('organization_id', auth()->user()->organization_id)->where('client_id', $request->client_id)->sum('amount_paid');
            $total_amount = Invoice::where('organization_id', auth()->user()->organization_id)->where('client_id', $request->client_id)->sum('amount');
            $last_purchase = $invoice->amount;
            $prev_balance = ($total_amount - $total_paid) + $last_paid;

        } else {

            $total_balance = 0;
            $prev_balance = 0;
            $balance = 0;
        }
        return response()->json(compact('client_invoices_payments','total_balance','prev_balance','last_paid', 'last_purchase', 'total_paid','total_amount'));
    }

    public function updateClientOnPayment(){
        
        $payments = Payment::where('organization_id', auth()->user()->organization_id)->get();


        foreach ($payments as $payment) {
            $invoice = Invoice::find($payment->invoice_id);
            if ($invoice) {
                $payment->update(['client_id' => $invoice->client_id]);
            }
        }

        return response()->json(compact('payments'));
    }

    public function clientsBalance(Request $request) {
        $balances = Invoice::select(
                'client_id',
                DB::raw('SUM(amount - amount_paid) as total_client_balance'),
                DB::raw('MAX(due_date) as due_date')
            )
            ->with('client')
            ->groupBy('client_id')
            ->havingRaw('SUM(amount - amount_paid) > 0')
            ->searchByClientName($request->search)
            ->filterDueDate($request->start_date, $request->end_date)
            ->paginate($request->rows, ['*'], 'page', $request->page);

        return response()->json(compact('balances'));
    }


}
