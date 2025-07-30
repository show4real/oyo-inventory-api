<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Product;
use App\Attribute;
use App\Category;
// use App\Brand;
use App\Supplier;
use Validator;
class ProductController extends Controller
{
    protected $product;
    
    public function __construct(Product $product)
    {
        $this->product = $product;
    }
    public function index(Request $request)
    {
        $products=$this->product
        ->where('organization_id', auth()->user()->organization_id)
        ->sort($request->sort)
        ->search($request->search)
        ->category($request->category)
        ->withCount('stocks')
        ->paginate($request->rows, ['*'], 'page', $request->page);
      
        return response()->json(compact('products'));
    }

    public function getAllProducts(){

        $products = Product::where('organization_id', auth()->user()->organization_id)->get();

        return response()->json(compact('products'));
    }

    public function show(Product $product){
        $product = $product->where('organization_id', auth()->user()->organization_id)->where('id', $product->id)->first();
        $attributes=Attribute::where('product_id',$product->id)
        ->with('attributevalues')->get();
        $categories=Category::select('id','name')->get();
        return response()->json(compact('product','attributes','categories'));
    }

   

    public function save(Request $request)
    {

        $supplier = Supplier::where('organization_id', auth()->user()->organization_id)->first();
    
        $product=$this->product;
        $product->supplier_id = $supplier->id;
        $product->deleted =0;
        $product->category_id = $request->category_id;
        $product->status =$request->status;
        $product->name = $request->product_name;
        $product->description = $request->description;
        $product->slug = str_slug($request->product_name, "-");
        $product->organization_id = auth()->user()->organization_id;
        $product->save();
        return response()->json(compact('product'));
    }

    
    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'unique:products'
        ]);

        $supplier = Supplier::where('organization_id', auth()->user()->organization_id)->first();

        $product = $this->product->findOrFail($id);
        $product->supplier_id = $supplier->id;
        $product->category_id = $request->category_id;
        $product->name = $request->name;
        $product->description = $request->description;
        $product->status =true;
        $product->slug = str_slug($request->slug, "-");
        $product->update();
        return response()->json(compact('product'));
    }

    public function delete($id, Request $request){
        $product = $this->product->where('organization_id', auth()->user()->organization_id)->findOrFail($id);
        $product->remove();
        return response()->json(true);
    }

}
