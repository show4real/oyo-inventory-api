<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable=['name','slug'];
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

    public function products(){
        return $this->hasMany('App\Product', 'brand_id');
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

    public function scopeBr($query, $filter){
        if($filter != null){
         return $query->select('id','name')->get();
        }else{
            return [];
        }
     }

}
