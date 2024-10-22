<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Attribute;
use App\product;
class AttributeController extends Controller
{
    public function index(Request $request){
        $attributes = [];
        if($request->product_id){
            $attributes=Attribute::where('product_id',$request->product_id)
            ->with('attributevalues')->paginate(10);
        }
        $products=Product::select('id','name')->get();
        return response()->json(compact('attributes','products'));
    }

    public function show(Attribute $attribute){
        $attribute = $attribute->where('id', $attribute->id)->first();
        return response()->json(compact('attribute'));
    }

    public function save(Request $request){
        foreach($request->name as $values) {

            $attributes[] = Attribute::updateOrCreate(
                ['name' => $values, 
                'slug' => str_slug($values),
                'product_id'=>$request['product_id'],
                ],

                ['name'=>$values,
                'slug'=>str_slug($values),
                'product_id'=>$request['product_id'],
                ]
            );
        }

        return response()->json(compact('attributes'));
    }

    public function update(Request $request, Attribute $attribute){

        $validator = Validator::make($request->all(), [
            'name' => 'unique:attributes,name,'. $attribute->id
        ]);

        if($validator->fails()){
          return response()->json($validator->messages(), 422);
        }
        $attribute->name = $request->name;
        $attribute->slug = str_slug($request->slug, "-");
        $save=$attribute->save();
        if($save){
            $products = Product::where('attribute_id', '=',$attribute->id)->first();
            if($products){
                foreach($products as $product) {
                    $product->attribute_id = $request->name;
                    $product->save();
                }
            }
        }
        return response()->json(compact('attribute'));
    }

    public function delete(Attribute $attribute){
        $attribute->delete();
        return response()->json(true);
    }
}
