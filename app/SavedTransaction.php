<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SavedTransaction extends Model
{
    protected $table = 'saved_transactions';

    protected $fillable = [
        'transaction_id',
        'organization_id',
        'user_id',
        'total_amount',
        'notes',
    ];

    public function items()
    {
        return $this->hasMany(SavedTransactionItem::class, 'saved_transaction_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeSearch($query, $filter)
    {
    	$searchQuery = trim($filter);
    	$requestData = ['transaction_id', 'notes'];
    	$query->when($filter!='', function ($query) use($requestData, $searchQuery) {
    		return $query->where(function($q) use($requestData, $searchQuery) {
    			foreach ($requestData as $field)
    				$q->orWhere($field, 'like', "%{$searchQuery}%");
    			});
    	});
    }
}