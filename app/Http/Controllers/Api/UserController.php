<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\User;
use App\Branch;

class UserController extends Controller
{
    public function index(Request $request){
        $users = User::
        search($request->search)
        ->latest()
        ->paginate(10);
        $branches=Branch::select('id','name')->get();
        return response()->json(compact('users','branches'));
    }

    public function show(User $user){
        $authuser = auth()->user();
        $user = User::where('id', $user->id)->first();
        return response()->json(compact('user'),200);
    }

    public function save(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users,email',
            'phone' => 'required|unique:users'
        ]);

      if($validator->fails()){
          return response()->json($validator->messages(), 422);
        }
       
        $user= new User();
        $user->admin=$request->admin;
        $user->status =$request->status;
        $user->firstname = $request->firstname;
        $user->branch_id = $request->branch_id;
        $user->lastname = $request->lastname;
        $user->address=$request->address;
        $user->phone = $request->phone;
        $user->email = $request->email;
        $user->password=bcrypt($request->password);
        $user->save();
        return response()->json(compact('user'));
    }

    public function update(Request $request, User $user){

        $validator = Validator::make($request->all(), [
            'email' => 'unique:users,email,'. $user->id
        ]);

        if($validator->fails()){
          return response()->json($validator->messages(), 422);
        }
       
        $user->status =$request->status;
        $user->admin =$request->admin;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->address = $request->address;
        $user->status=$request->status;
        $user->branch_id = $request->branch_id;
        if($request->password){
            $user->password=bcrypt($request->password);
        }
        $user->save();
        return response()->json(compact('user'));
    }
    public function search(Request $request){
        $users = User::search($request->search)->get();

        return response()->json(compact('users'));
    }

    public function delete($id, Request $request){
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(true);
    }

}
