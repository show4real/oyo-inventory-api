<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\attributeValue;
use App\Product;


class attributeValueController extends Controller
{
    
    public function index(Request $request){
        $attributevalues = AttributeValue::withCount('products')
            ->search($request->search)
            ->paginate(10);
        return response()->json(compact('attributevalues'));
    }

    public function show(AttributeValue $attributeValue){
        $attributeValue = AttributeValue::where('id', $attributeValue->id)->first();
        return response()->json(compact('attributeValue'));
    }

    public function save(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'unique:attribute_values'
        ]);

        if($validator->fails()){
          return response()->json($validator->messages(), 422);
        }
        /*$check_row= AttributeValue::where([
            ['column_1', '=', 'value_1'],
            ['column_2', '<>', 'value_2'],
        ])*/

        AttributeValue::where('attribute_id', $request->attribute_id)->delete();
        foreach($request->values as $values){
            $attributeValue = new AttributeValue();
            $attributeValue->varianttype_id = $variant_type_id;
            $attributeValue->product_id = $product_id;
            $attributeValue->values = $values;
            $attributeValue->save();
        }

        return response()->json(compact('attributeValue'));
    }

    public function update(Request $request, AttributeValue $attributeValue){

        $validator = Validator::make($request->all(), [
            'name' => 'unique:attribute_values,name,'. $attributeValue->id
        ]);

        if($validator->fails()){
          return response()->json($validator->messages(), 422);
        }
        $name=$attributeValue->name;
        $attributeValue->name = $request->name;
        $attributeValue->slug = str_slug($request->slug, "-");
        $save=$attributeValue->save();
        if($save){
            $products = Product::where('attributeValue_id', '=',$attributeValue->id)->first();
            if($products){
                foreach($products as $product) {
                    $product->attributeValue_id = $request->name;
                    $product->save();
                }
            }
        }
        return response()->json(compact('attributeValue'));
    }

    public function delete(AttributeValue $attributeValue){
        $attributeValue->delete();
        return response()->json(true);
    }
}
