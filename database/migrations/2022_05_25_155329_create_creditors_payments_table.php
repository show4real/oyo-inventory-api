<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditorsPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('creditors_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('creditor_id');
            $table->bigInteger('amount');
            $table->bigInteger('amount_paid');
            $table->bigInteger('balance');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('creditors_payments');
    }
}
