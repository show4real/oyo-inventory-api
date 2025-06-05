<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('session_id');
            $table->unsignedBigInteger('cashier_id')->nullable();
            $table->unsignedBigInteger('game_id');
            $table->integer('rounds');
            $table->decimal('price_per_round', 8, 2);
            $table->decimal('total_price', 10, 2);
            $table->timestamp('played_at')->nullable();
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
        Schema::dropIfExists('bookings');
    }
}
