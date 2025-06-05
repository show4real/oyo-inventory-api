<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = ['user_id', 'name', 'price'];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
