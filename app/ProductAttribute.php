<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductAttribute extends Model
{
    use SoftDeletes;
    //protected $appends = [''];
    protected $hidden =['attribute','deleted_at','attribute_id'];

    protected $fillable = [
        'id','product_id', 'attribute_id', 'attribute_value'
    ];
    public function attribute(){
        return $this->belongsTo('App\Attribute', 'attribute_id')->select('id','name');
    }

    /*public function getAttributeNameAttribute()
    {
        if($this->attribute){
            return $this->attribute->name;
        }
    }*/

    public function product(){
        return $this->belongsTo('App\Product', 'product_id')
        ->select('id','name','category_id','brand_id','description');
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
}
