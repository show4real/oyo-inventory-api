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
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(Request $request){
        $clients = Client::where('organization_id', auth()->user()->organization_id)
            ->withCount(['invoices as invoice_count'])
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


    public function save(Request $request)
    {
        $orgId = auth()->user()->organization_id;

    
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'nullable',
                'email',
                Rule::unique('clients')->where(function ($q) use ($orgId) {
                    return $q->where('organization_id', $orgId);
                }),
            ],

            'phone' => [
                'nullable',
                Rule::unique('clients')->where(function ($q) use ($orgId) {
                    return $q->where('organization_id', $orgId);
                }),
            ],

            'address' => 'nullable|string',
        ]);

       
        $normalizedName  = strtolower(trim($request->name));
        $normalizedEmail = strtolower(trim($request->email));

        if ($normalizedName === 'general client' ||
            $normalizedEmail === 'generalclient@gmail.com') {

            $exists = Client::where('organization_id', $orgId)
                ->where(function ($q) use ($normalizedName, $normalizedEmail) {
                    $q->whereRaw('LOWER(name) = ?', [$normalizedName])
                    ->orWhereRaw('LOWER(email) = ?', [$normalizedEmail]);
                })
                ->exists();

            if ($exists) {
                return response()->json([
                    'status'  => false,
                    'message' => 'General Client or its email already exists for this organization.'
                ], 422);
            }
        }

        $client = Client::create([
            'name'            => $request->name,
            'email'           => $request->email,
            'phone'           => $request->phone,
            'address'         => $request->address,
            'organization_id' => $orgId,
        ]);

        return response()->json([
            'status' => true,
            'client' => $client
        ], 201);
    }



    public function update(Request $request, Client $client){

        $orgId = auth()->user()->organization_id;

        // ensure client belongs to the user's organization
        if ($client->organization_id !== $orgId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Disable editing if this is the General Client record for the org
        if (strtolower(trim($client->name)) === 'general client' ||
            strtolower(trim($client->email ?? '')) === 'generalclient@gmail.com') {
            return response()->json(['error' => 'Cannot edit General Client'], 422);
        }

        // validate incoming data (respecting uniqueness within organization, ignoring this client)
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'nullable',
                'email',
                Rule::unique('clients')->where(function ($q) use ($orgId) {
                    return $q->where('organization_id', $orgId);
                })->ignore($client->id),
            ],

            'phone' => [
                'nullable',
                Rule::unique('clients')->where(function ($q) use ($orgId) {
                    return $q->where('organization_id', $orgId);
                })->ignore($client->id),
            ],

            'address' => 'nullable|string',
        ]);

        $client->name = $request->name;
        $client->email = $request->email;
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
        $orgId = auth()->user()->organization_id;
        $client = Client::findOrFail($id);

        // ensure client belongs to the user's organization
        if ($client->organization_id !== $orgId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Prevent deleting the general client
        if (strtolower(trim($client->name)) === 'general client') {
            return response()->json(['error' => 'Cannot delete General Client'], 422);
        }

        // Find or create the "General Client" for this organization
        $generalClient = Client::where('organization_id', $orgId)
            ->whereRaw('LOWER(name) = ?', ['general client'])
            ->first();

        if (! $generalClient) {
            $generalClient = Client::create([
                'name'            => 'General Client',
                'email'           => 'generalclient@gmail.com',
                'phone'           => null,
                'address'         => null,
                'organization_id' => $orgId,
            ]);
        }

        // Reassign invoices and payments to the general client
        Invoice::where('organization_id', $orgId)
            ->where('client_id', $client->id)
            ->update(['client_id' => $generalClient->id]);

        Payment::where('organization_id', $orgId)
            ->where('client_id', $client->id)
            ->update(['client_id' => $generalClient->id]);

        // Delete the client
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
        $orgId = (int) auth()->user()->organization_id;

        // due_date reflects the client's LAST transaction (most recent invoice),
        // not MAX(due_date) across all invoices: an older invoice can have a later
        // due date and would otherwise win. A correlated subquery picks the
        // due_date of the newest invoice per client (created_at, tie-broken by id).
        $balances = Invoice::where('organization_id', $orgId)
                ->select(
                'client_id',
                DB::raw('SUM(amount - amount_paid) as total_client_balance'),
                DB::raw('(SELECT i2.due_date FROM invoices i2
                          WHERE i2.client_id = invoices.client_id
                            AND i2.organization_id = ' . $orgId . '
                            AND i2.deleted_at IS NULL
                          ORDER BY i2.created_at DESC, i2.id DESC
                          LIMIT 1) as due_date')
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
