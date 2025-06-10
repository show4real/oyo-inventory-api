<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BookingTransaction extends Model
{
    protected $table = "booking_transactions";

    protected $appends = ['cashier'];

    protected $fillable = ['session_id', 'total_price','cashier_id','payment_mode'];

    public function getCashierAttribute(){

        $user = User::where('id', $this->cashier_id)->first();

        return $user->name ?? "N/A";
    }

    public function scopeSoldBy($query, $cashierId)
    {
        return $query->where('cashier_id', $cashierId);
    }


    public function scopeDateBetween($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}
