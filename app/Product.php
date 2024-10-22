<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\ProductImage;
class Product extends Model
{
    use SoftDeletes;

    protected $appends = ['category_name','brand_name','product_image'];
    
    protected $hidden = ['cat','bra','created_at','updated_at'];

    public function category(){
        return $this->belongsTo('App\Category', 'category_id')->select('id','name');
    }

    public function bra(){
        return $this->belongsTo('App\Brand', 'brand_id');
    }

    public function cat(){
        return $this->belongsTo('App\Category', 'category_id')->select('id','name');
    }

    public function brand(){
        return $this->belongsTo('App\Brand', 'brand_id');
    }

    
    public function getCategoryNameAttribute()
    {
        if($this->cat){
            return $this->cat->name;
        }
    }



    public function getBrandNameAttribute()
    {
        if($this->bra){
            return $this->bra->name;
        }
    }

    


    public function scopeSearch($query, $filter)
    {
    	$searchQuery = trim($filter);
    	$requestData = ['name'];
    	$query->when($filter!='', function ($query) use($requestData, $searchQuery) {
    		return $query->where(function($q) use($requestData, $searchQuery) {
    			foreach ($requestData as $field)
    				$q->orWhere($field, 'like', "%{$searchQuery}%");
    			});
    	});
    }

    public function scopeCategory($query, $filter){
        if($filter != null){
         return $query->where("category_id",$filter)->latest();
        }
     }

     

     

     public function scopeBrand($query, $filter){
        if($filter != null){
         return $query->where("brand_id",$filter)->latest();
        }
     }

    public function getProductImageAttribute(){
        if($this->id){
            $pImage=ProductImage::where('product_id',$this->id)
            ->select('url')->first();
            if($pImage){
                return $pImage->url;
            }
        }
    }


    public function scopeStatus($query,$status)
    {

        $query->when($status!='', function ($query) use($status) {
            if($status == "true"){
               return $query->where('status',true);
            }else if($status == "false"){
                return $query->where('status',false);
            }
        });

    }

    public function scopeSort($query, $filter)
	{
        if($filter ==''){
            return $query->orderBy('id', 'DESC');
        }elseif($filter=='recent'){
			return $query->latest();
		} elseif($filter=='name-desc'){
            return $query->orderBy('name', 'DESC');
        } elseif($filter=='name'){
			return $query->orderBy('name', 'ASC');
		} elseif($filter=='oldest'){
            return $query->orderBy('id','asc');
        
		}
    }

    public function attributes(){
        return $this->hasMany('App\ProductAttribute','product_id');
    }

    public function pAttributes(){
        return $this->hasMany('App\Attribute','product_id');
    }

    public function purchaseOrders(){
        return $this->hasMany('App\PurchaseOrder','product_id');
    }

    public function stocks(){
        return $this->hasMany('App\Stock','product_id');
    }


    public function sales(){
        return $this->hasMany('App\Pos','product_id');
    }

    public function creditors(){
        return $this->hasMany('App\Creditor','product_id');
    }

    public function pImage(){
        return $this->hasMany('App\ProductImage', 'product_id')->select('id','url');
    }

    public function scopeRemove() {
      
        $this->purchaseOrders()->delete();
        $this->sales()->delete();
         $this->creditors()->delete();
        $this->stocks()->delete();
        $this->pAttributes()->delete();
        $this->attributes()->delete();
        $this->pImage()->delete();

        parent::delete();
    }

    
}
