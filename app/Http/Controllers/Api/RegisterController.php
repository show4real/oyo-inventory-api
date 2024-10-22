<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Validator;
use Mail;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users,email',
            'phone' => 'required|unique:users'
        ]);

      if($validator->fails()){
          return response()->json($validator->messages(), 422);
        }
       
        $user= new User();
        //$user->admin=$request->admin;
        //$user->status =$request->status;
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->address=$request->address;
        $user->phone = $request->phone;
        $user->email = $request->email;
        $user->password=bcrypt($request->password);
        $user->role=$request->role;
        $save= $user->save();
        if($save){
            if(filter_var($request->get('email'), FILTER_VALIDATE_EMAIL)){
                $credentials = ['email' => strtolower($request->get('email')), 'password' => $request->get('password')];
              } else {
                $credentials = ['phone' => $request->get('phone'), 'password' => $request->get('password')];
              }
              if ((! $token = auth()->attempt($credentials))) {
                return response()->json(['error' => 'Unauthorized'], 401);
              }
          
              $userFromToken = auth()->user();
              return response()->json(compact('user','token'));
        }
       
    }


}
