<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ProductAttribute;
use Validator;
class ProductAttributeController extends Controller
{
    public function index(Request $request){
        $productAttributes = ProductAttribute::with('attribute')
            ->with('product')
            ->search($request->search)
            ->paginate(10);
        return response()->json(compact('productAttributes'));
    }

    public function show($productAttribute){

        $productAttribute = ProductAttribute::where('product_id', $productAttribute)
            ->with('product')
            ->with('attribute')->get();
        if($productAttribute){
            return response()->json(compact('productAttribute'),200);
        }else{
            $message="Attribute not found";
            return response()->json(compact('message'),404);
        }
        
    }

    public function save(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'unique:product_attributes'
        ]);

        if($validator->fails()){
          return response()->json($validator->messages(), 422);
        
        }
        foreach($request->values as $values) {

            $productAttribute = ProductAttribute::updateOrCreate(
                ['product_id' => $request->product_id, 
                'attribute_id' => $request->attribute_id,
                'attribute_value'=>$values],

                ['attribute_value'=>$values,
                'product_id'=>$request['product_id'],
                'attribute_id'=>$request['attribute_id'],
                ]
            );
        }
        return response()->json(compact('productAttribute'));

        
        
    }

    
    public function delete(AttributeValue $attributeValue){
        $attributeValue->delete();
        return response()->json(true);
    }
}
