<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Brand;
use App\Product;
use Validator;

class BrandController extends Controller
{
    public function index(Request $request){
        $brands = Brand::withCount('products')
            ->search($request->search)
            ->paginate(10);
        return response()->json(compact('brands'));
    }

    public function show(Brand $brand){
        $brand =$brand->where('id', $brand->id)->first();
        return response()->json(compact('brand'));
    }

    public function save(Request $request){
        foreach($request->name as $values) {

            $brands[] = Brand::updateOrCreate(
                ['name' => $values, 
                'slug' => str_slug($values),
                ],

                ['name'=>$values,
                'slug'=>str_slug($values),
                ]
            );
        }

        return response()->json(compact('brands'));
    }

    public function update(Request $request, Brand $brand){

        $validator = Validator::make($request->all(), [
            'name' => 'unique:brands,name,'. $brand->id
        ]);

        if($validator->fails()){
          return response()->json($validator->messages(), 422);
        }
        $brand->name = $request->name;
        $brand->slug = str_slug($request->name, "-");
        $save=$brand->save();
        
        return response()->json(compact('brand'));
    }

    public function delete(Brand $brand){
        $brand->delete();
        return response()->json(true);
    }
}
