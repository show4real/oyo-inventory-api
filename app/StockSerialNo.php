<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class StockSerialNo extends Model
{
    use SoftDeletes;
    protected $table='stock_serial_no';
    protected $fillable = ["serial_no",'stock_id'];
}
