<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'session_id', 'game_id', 'rounds',
        'price_per_round', 'total_price', 'played_at','cashier_id'
    ];

    protected $appends = ['cashier', 'name'];

    protected $casts = [
        'price_per_round' => 'integer',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function getCashierAttribute(){
       
        $cashier = User::where('id', $this->cashier_id)->first();

        return $cashier->name ?? 'N/A';
    }

    public function getNameAttribute(){
       
        $game = Game::where('id', $this->game_id)->first();

        return $game->name ?? 'N/A';
    }


    public function scopeSoldBy($query, $cashierId)
    {
        return $query->where('cashier_id', $cashierId);
    }


    public function scopeDateBetween($query, $from, $to)
    {
        return $query->whereBetween('played_at', [$from, $to]);
    }
}
