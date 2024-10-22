<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceItem extends Model
{
    use SoftDeletes;
    protected $table='invoiceitems';
    protected $fillable=['invoice_id','name','description','quantity','rate','unit','amount'];
}
