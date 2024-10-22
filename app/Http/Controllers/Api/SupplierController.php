<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Supplier;
use Str;
use Validator;

class SupplierController extends Controller
{
    protected $supplier;
    
    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    public function index(Request $request){
        $suppliers = Supplier::withCount('orders')
            ->search($request->search)
            ->paginate($request->rows, ['*'], 'page', $request->page);
        return response()->json(compact('suppliers'));

    }

    public function show(Supplier $supplier){
        
        $supplier = Supplier::where('id', $supplier->id)->first();
        if($supplier){
            
            return response()->json(compact('supplier'),200);
        }else{
            return response()->json("Not found",404);
        }
        
    }

    public function save(Request $request){
       
        foreach($request->name as $values) {

            $suppliers[] = Supplier::updateOrCreate(
                ['name' => $values, 
                ],

                ['name'=>$values,
                'supplier_id'=>"SUP-TRK-" . strtoupper(Str::random(5))
                ]
            );
        }

        return response()->json(compact('suppliers'));
    }

    

    public function update(Request $request, Supplier $supplier){

        $validator = Validator::make($request->all(), [
            'name' => 'unique:supplier,name,'. $supplier->id,
            'email' => 'unique:supplier,email,'. $supplier->id
        ]);

        if($validator->fails()){
          return response()->json($validator->messages(), 422);
        }
        $supplier = $this->supplier->findOrFail($supplier->id);
        $supplier->name = $request->name;
        $supplier->city = $request->city;
        $supplier->country = $request->country;
        $supplier->country_code = $request->country_code;
        $supplier->phone = $request->phone;
        $supplier->email = $request->email;
        $supplier->state = $request->state;
        $supplier->street_address = $request->street_address;
        $supplier->zip = $request->zip;
        $supplier->description = $request->description;
        $supplier->save();
        return response()->json(compact('supplier'));
    }

    public function delete(Supplier $supplier){

        $supplier->remove();
        // $transaction= Pos::where('')
        return response()->json(true);
    }
}
