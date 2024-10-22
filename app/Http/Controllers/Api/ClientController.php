<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Client;
use Validator;
use App\User;

class ClientController extends Controller
{
    public function index(Request $request){
        $clients = Client::
        search($request->search)
        ->latest()
        ->paginate($request->rows, ['*'], 'page', $request->page);
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
}
