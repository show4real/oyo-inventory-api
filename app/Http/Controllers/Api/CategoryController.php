<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Category;
use App\Product;
use Validator;


class CategoryController extends Controller
{
    public function index(Request $request){
        $categories = Category::withCount('products')
            ->search($request->search)
            ->paginate(20);
        return response()->json(compact('categories'));
    }

    public function show(Category $category){
        $category = Category::where('id', $category->id)->first();
        return response()->json(compact('category'));
    }

    public function save(Request $request){
       
        foreach($request->name as $values) {

            $categories[] = Category::updateOrCreate(
                ['name' => $values, 
                'slug' => str_slug($values),
                ],

                ['name'=>$values,
                'slug'=>str_slug($values),
                ]
            );
        }

        return response()->json(compact('categories'));
    }

    public function update(Request $request, Category $category){

        $validator = Validator::make($request->all(), [
            'name' => 'unique:categories,name,'. $category->id
        ]);

        if($validator->fails()){
          return response()->json($validator->messages(), 422);
        }
        $category->name = $request->name;
        $category->slug = str_slug($request->name, "-");
        $category->save();
        return response()->json(compact('category'));
    }

    public function delete(Category $category){
        $category->delete();
        return response()->json(true);
    }
}
