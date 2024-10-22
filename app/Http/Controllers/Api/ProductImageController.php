<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Product;
use App\ProductImage;
use Str;
use Storage;

class ProductImageController extends Controller
{
    public function store($product_id,Request $request)
    {
        $product_name=Product::where('id',$product_id)->first()->name;
        $check=ProductImage::where('product_id',$product_id)->get();
        $folder = "/productimages/";
        $data = $request->data;
        $img=preg_replace('#^data:image/[^;]+;base64,#', '', $data);
        $type=explode(';',$data)[0];
        $type=explode('/',$type)[1];
        $name=Str::random(28);
        $path = $folder . $name.'.'.$type;
        $url=url('/');
        $product_image = ProductImage::updateOrCreate(
            ['product_id' => $product_id],
            ['product_id' => $product_id, 'url' => $url."/storage".$path]
        );
        Storage::disk('public')->put($path,base64_decode($img));
        return response()->json(compact('product_image'),200);
    }
}
