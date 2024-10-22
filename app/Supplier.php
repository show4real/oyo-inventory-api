<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Product;
use Illuminate\Database\Eloquent\SoftDeletes;
class Supplier extends Model
{
    use SoftDeletes;
    protected $table="supplier";
    protected $fillable=['name','supplier_id'];
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
        return $this->hasMany('App\Product', 'supplier_id');
    }

    public function orders(){
        return $this->hasMany('App\PurchaseOrder', 'supplier_id');
    }

    public function sales(){
        return $this->hasMany('App\Pos', 'supplier_id');
    }

    public function stocks(){
        return $this->hasMany('App\Stock', 'supplier_id');
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

    public function purchaseOrders(){
        return $this->hasMany('App\PurchaseOrder', 'supplier_id');
    }

    public function scopeRemove() {
        $this->orders()->delete();
        $this->sales()->delete();
        $this->stocks()->delete();
        parent::delete();
    }


}
