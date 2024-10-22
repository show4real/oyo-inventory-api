<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockSerialNoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_serial_no', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('stock_id')->nullable();
            $table->integer('status')->nullable();
            $table->string('serial_no')->nullable();
            $table->string('sold_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_serial_no');
    }
}
