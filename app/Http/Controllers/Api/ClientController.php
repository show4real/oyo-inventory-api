<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Client;
use App\Invoice;
use Validator;
use App\User;
use App\Payment;

class ClientController extends Controller
{
    public function index(Request $request){
        $clients = Client::
        search($request->search)
        ->latest()
        ->paginate($request->rows, ['*'], 'page', $request->page);
        return response()->json(compact('clients'));
    }

    public function allClients(Request $request){
        $clients = Client::
        search($request->search)
        ->latest()
        ->get();
        return response()->json(compact('clients'));
    }

    public function cashiers(Request $request){
        $cashiers = User::
        search($request->search)
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
        $client->save();
        return response()->json(compact('client'));
    }

    public function update(Request $request, Client $client){

        $validator = Validator::make($request->all(), [
            'email' => 'unique:clients,email,'. $client->id
        ]);

        if($validator->fails()){
          return response()->json($validator->messages(), 422);
        }
       
        $client->name=$request->name;
        $client->email =$request->email;
        $client->phone = $request->phone;
        $client->address = $request->address;
        $client->save();
        return response()->json(compact('client'));
    }
    public function search(Request $request){
        $clients = Client::search($request->search)->get();

        return response()->json(compact('clients'));
    }

    public function delete($id, Request $request){
        $client = Client::findOrFail($id);
        $client->delete();
        return response()->json(true);
    }

    public function clientPayments(Request $request){

        $client_invoices_payments = Invoice::where('client_id', $request->client_id)
            ->with('payments')
            ->latest()
            ->paginate($request->rows, ['*'], 'page', $request->page);

        $clientInvoices = Invoice::where('client_id', $request->client_id)
                ->get();

        $invoice = Invoice::where('client_id', $request->client_id)->latest()->first();

        $payment = Payment::where('client_id', $request->client_id)->latest()->first();

        if($invoice){
             
            $total_balance = $clientInvoices->sum('client_balance');
        
            $balance = $invoice->amount - $invoice->amount_paid;

            $prev_balance = $total_balance - $balance;

            $last_paid = $payment->amount_paid ?? 0;

        } else {

            $total_balance = 0;
            $prev_balance = 0;
            $balance = 0;
        }
        return response()->json(compact('client_invoices_payments','total_balance','balance','prev_balance','last_paid'));
    }

    public function updateClientOnPayment(){
        
        $payments = Payment::get();


        foreach ($payments as $payment) {
            $invoice = Invoice::find($payment->invoice_id);
            if ($invoice) {
                $payment->update(['client_id' => $invoice->client_id]);
            }
        }

        return response()->json(compact('payments'));
    }
}
