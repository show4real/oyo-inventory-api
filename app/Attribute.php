<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $table="attributes";
    //protected $appends=[''];
    protected $hidden=['created_at','updated_at','deleted_at','slug'];
    protected $fillable=["name","slug","product_id"];
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

    public function attributevalues(){
        return $this->hasMany('App\ProductAttribute', 'attribute_id')->select(['attribute_id','attribute_value']);
    }
    
    public function scopeGetAttribute($request){
        return $query->where('product_id',$request->product_id)->with('attributevalues')->get();
    }

    public function products(){
        return $this->hasMany('App\ProductAttribute', 'product_id');
    }

}
