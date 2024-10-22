<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

     
    protected $fillable = [
        'firstname', 'lastname', 'address', 'phone', 'email', 'password'
    ];

   

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'admin' => 'integer',
        'id'=>'integer'
    ];
    protected $dates = [
        'created_at',
        'updated_at',
        'recovery_expiry'
    ];

    protected $appends = [
        'name','branch_name'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }



    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getNameAttribute()
    {
      return $this->firstname.' '.$this->lastname;
    }

    public function scopeSearch($query, $filter)
    {
    	$searchQuery = trim($filter);
    	$requestData = ['firstname', 'lastname', 'email', 'phone'];
    	$query->when($filter!='', function ($query) use($requestData, $searchQuery) {
    		return $query->where(function($q) use($requestData, $searchQuery) {
    			foreach ($requestData as $field)
    				$q->orWhere($field, 'like', "%{$searchQuery}%");
    			});
    	});
    }

    public function branch(){
        return $this->belongsTo('App\Branch', 'branch_id')->select('id','name');
    }

    public function getBranchNameAttribute(){
        $branch = Branch::where('id',$this->branch_id)->first();
        if($branch)
        return $branch->name;
    }

    public function phone(){
        return $this->hasOne('App\Phone');
    }

}
