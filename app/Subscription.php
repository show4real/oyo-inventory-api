<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Subscription extends Model
{

    protected $fillable = [
        'organization_id',
        'user_id',
        'plan',
        'start_date',
        'expiry_date',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->expiry_date && Carbon::now()->lessThanOrEqualTo(Carbon::parse($this->expiry_date));
    }
}
